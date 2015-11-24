<?php
namespace Octava\Bundle\MuiBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Octava\Bundle\MuiBundle\LocaleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Механизм обработки мультиязычных
 * данных в форме с использованием событий
 * 1. Обработчик события POST_SET_DATA
 * вызывается после передачи в форму выводимого объекта
 * с мультиязычными полями. По вызову
 * этого события получаются мультиязычные данные объекта
 * и устанавливаются значениями мультиязычных
 * полей формы. Данные поля имеют структуру названий:
 * <field>___<locale>, например title___ru.
 * 2. Обработчик PRE_SUBMIT запускается при
 * отправке формы и сохраняет данные мультиязычных
 * полей формы с помощью репозитория
 * Gedmo\Translatable\Entity\Translation.
 */
class AddTranslationFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var PropertyMappingFactory
     */
    private $mappingFactory;

    /**
     * @var array
     */
    private $fileMappingCache;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @var array
     */
    private $newData = [];

    public function __construct(
        EntityManager $em,
        LocaleManager $localeManager,
        PropertyMappingFactory $mappingFactory,
        FileStorageInterface $fileStorage
    ) {
        $this->em = $em;
        $this->localeManager = $localeManager;
        $this->mappingFactory = $mappingFactory;
        $this->fileStorage = $fileStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::POST_SUBMIT => 'postSubmit',
        ];
    }

    public function postSetData(FormEvent $event)
    {
        $object = $event->getData();
        $form = $event->getForm();
        if ($object === null) {
            return;
        }

        $repository = $this->em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $repository->findTranslations($object);

        $fileMappingsFields = array_keys($this->getFileMappings($object));

        foreach ($this->localeManager->getActiveList() as $localeData) {
            $locale = $localeData->getAlias();

            if (!isset($translations[$locale])) {
                continue;
            }

            foreach ($translations[$locale] as $field => $value) {
                if ($form->has('translation___' . $field . '___' . $locale)) {
                    if (in_array($field, $fileMappingsFields)) {
                        $value = unserialize($value);
                    } else {
                        $value = $this->typeCast($object, $field, $value);
                    }
                    $form->get('translation___' . $field . '___' . $locale)->setData($value);
                }
            }
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $entity = $form->getData();

        $fileMappingsFields = array_keys($this->getFileMappings($entity));
        foreach ($data as $fieldAlias => $value) {
            if (preg_match('|^translation___([a-z][a-z_]+[a-z])___([a-z]{2})$|ius', $fieldAlias, $matches)) {
                list(, $fieldName, $locale) = $matches;

                if (in_array($fieldName, $fileMappingsFields)) {
                    $entity->{'set' . ucfirst($fieldName)}(null);
                }
                // Хак для обхода ограничения на сохранение
                // пустых строк для языка по-умолчанию
                $value = $value === '' ? false : $value;
                $this->newData[$locale][$fieldName] = $value;
            }
        }
        $event->setData($data);
    }

    public function postSubmit(FormEvent $event)
    {
        $object = $event->getData();
        /** @var TranslationRepository $repository */
        $repository = $this->em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $repository->findTranslations($object);

        $fileMappingsFields = array_keys($this->getFileMappings($object));
        foreach ($this->newData as $locale => $localeData) {
            foreach ($localeData as $field => $value) {
                if (in_array($field, $fileMappingsFields)) {
                    $value = $this->processFileUpload($value, $field, $locale, $object);
                    if ($this->localeManager->getDefaultLocaleAlias() == $locale && is_array($value)) {
                        $object->{'set' . ucfirst($field)}($value);
                    }
                    if (empty($value['fileName'])) {
                        $value = false;
                    }
                }
                if ($value !== false) {
                    $repository->translate($object, $field, $locale, $value);
                } else {
                    if (array_key_exists($locale, $translations) &&
                        array_key_exists($field, $translations[$locale])
                    ) {
                        $repository->createQueryBuilder('t')
                            ->where('t.field = :field')
                            ->andWhere('t.locale = :locale')
                            ->andWhere('t.objectClass = :class')
                            ->andWhere('t.foreignKey = :foreignKey')
                            ->setParameters([
                                'class' => get_class($object),
                                'field' => $field,
                                'locale' => $locale,
                                'foreignKey' => $object->getId()
                            ])
                            ->delete()->getQuery()->execute();
                    }
                }
            }
        }
    }

    protected function typeCast($object, $field, $value)
    {
        /* @var ClassMetadata $meta */
        $meta = $this->em->getClassMetadata(get_class($object));
        $type = $meta->getTypeOfField($field);
        $value = $this->em->getConnection()->convertToPHPValue($value, $type);
        $result = $value;
        return $result;
    }

    /**
     * Получить список полей типа
     * iphp_file с описанием связей
     * @param $entity
     * @return PropertyMapping[]
     */
    protected function getFileMappings($entity)
    {
        if (!is_null($this->fileMappingCache)) {
            return $this->fileMappingCache;
        }

        $mappings = $this->mappingFactory->getMappingsFromObject(
            $entity,
            new \ReflectionClass(get_class($entity))
        );

        $properties = [];
        foreach ($mappings as $mapping) {
            $properties[$mapping->getFileUploadPropertyName()] = $mapping;
        }

        return $this->fileMappingCache = $properties;
    }

    /**
     * Обработать мультиязычную загрузку файла
     * и получить подготовленные данные для сохранения
     * @param string $value - значение пришедшее из формы
     * @param string $field - обрабатываемое поле
     * @param string $locale - локаль
     * @param object $entity - сущность которая сохраняется
     * @return array|null
     */
    protected function processFileUpload($value, $field, $locale, $entity)
    {
        $result = false;
        if (isset($value['delete']) && $value['delete']) {
            $this->deleteFileByOldData($value, $field, $entity);
            //чтобы фронт не ломался и не было NOTICE в iPhp
            $result = [
                'fileName' => null,
                'originalName' => null,
                'mimeType' => null,
                'size' => null,
                'path' => null
            ];
        } elseif (isset($value['file']) && $value['file'] instanceof File) {
            // Локаль указвыается в сущности для того,
            // чтобы её можно было использовать
            // при формировании имени файла
            $entity->setLocale($locale);
            $fileMapping = $this->getFileMappings($entity)[$field];

            $fileData = $this->fileStorage->upload(
                $fileMapping,
                $value['file']
            );
            //Сбрасываем локаль
            $entity->setLocale(null);
            $result = $fileData;
        } elseif (isset($value['oldData'])) {
            $result = unserialize($value['oldData']);
        }
        return $result;
    }

    protected function deleteFileByOldData($value, $field, $entity)
    {
        if (empty($value['oldData'])) {
            return;
        }

        $fileMapping = $this->getFileMappings($entity)[$field];
        $oldData = unserialize($value['oldData']);

        $oldFilePath = $fileMapping->getConfig()['upload_dir'] . $oldData['fileName'];
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }
}

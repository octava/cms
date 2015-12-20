<?php
namespace Octava\Bundle\MuiBundle\Form;

use Doctrine\ORM\EntityManager;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Octava\Bundle\AdministratorBundle\Entity\Administrator;
use Octava\Bundle\MuiBundle\Form\EventListener\AddTranslationFieldSubscriber;
use Octava\Bundle\MuiBundle\LocaleManager;
use Octava\Bundle\MuiBundle\Validator\Constraints\FileNotBlank;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Механизм добавления мультиязычных
 * вкладок и полей в формы админки
 * Интерфейс класса повторяет аналогичный
 * интерфейс маппера форм Сонаты для упрощения работы
 * Класс зарегистрирован как сервис и может
 * быть получен по алиасу octava_mui.form.translation_mapper
 */
class TranslationMapper
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
     * @var FormMapper
     */
    private $formMapper;

    /**
     * Паттерн для названия мультиязычных вкладок
     * @var string
     */
    private $tabName;

    private $mappingFactory;

    private $fileStorage;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $fields = [];

    public function __construct(
        EntityManager $em,
        LocaleManager $localeManager,
        PropertyMappingFactory $mappingFactory,
        FileStorageInterface $fileStorage,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->localeManager = $localeManager;
        $this->mappingFactory = $mappingFactory;
        $this->fileStorage = $fileStorage;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormMapper $formMapper
     * @return $this
     */
    public function setFormMapper(FormMapper $formMapper)
    {
        $this->formMapper = $formMapper;

        return $this;
    }

    /**
     * Задать паттерн названия мультиязычных вкладок
     * @desc Если значение не задано вкладки
     * будут названы по названию локали
     * Для создания паттерна в строку
     * добавляется плейсхолдер %locale_name%
     * Например: "%locale_name% текст"
     * @param string $tabName
     * @return $this
     */
    public function with($tabName = '')
    {
        $this->tabName = $tabName;

        return $this;
    }

    /**
     * Добавить данные поля
     * @desc Метод повторяет сигнатуру
     * аналогичного метода класса FormMapper Сонаты
     * @param string $name
     * @param string $type
     * @param array $options
     * @param array $fieldDescriptionOptions
     * @return $this
     */
    public function add($name, $type = 'text', array $options = [], array $fieldDescriptionOptions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'type' => $type,
            'options' => $options,
            'fieldDescriptionOptions' => $fieldDescriptionOptions,
        ];

        return $this;
    }

    /**
     * Закончить задание содержания мультиязычной
     * вкладки и передать результат в мапер формы
     * @desc Метод повторяет поведение класса FormMapper
     * @return FormMapper
     */
    public function end()
    {
        $eventSubscriber = new AddTranslationFieldSubscriber(
            $this->em,
            $this->localeManager,
            $this->mappingFactory,
            $this->fileStorage
        );
        $this->formMapper->getFormBuilder()->addEventSubscriber($eventSubscriber);

        $firstTab = true;
        $locales = $this->localeManager->getActiveList();
        $administrator = $this->getTokenStorage()->getToken()->getUser();
        if ($administrator instanceof Administrator) {
            usort($locales, [$administrator, 'sortLocales']);
        }
        foreach ($locales as $localeData) {
            $tabName = $localeData->getName();
            if (strlen($this->tabName) > 0) {
                $tabName = str_replace('%locale_name%', $localeData->getName(), $this->tabName);
            }

            $tabOptions = [];
            if ($this->isDisabledByAdministratorLocales($localeData->getAlias())) {
                $tabOptions = ['class' => 'disabled'];
            }

            $this->formMapper->with($tabName, $tabOptions);

            foreach ($this->fields as $fieldData) {
                if (!$firstTab && (!isset($fieldData['options']['translatable'])
                        || !$fieldData['options']['translatable'])
                ) {
                    continue;
                }

                $fieldName = $fieldData['name'];
                if (isset($fieldData['options']['translatable'])) {
                    if ($fieldData['options']['translatable']) {
                        $fieldName = 'translation___'.$fieldData['name'].'___'.$localeData->getAlias();
                        $fieldData['options']['mapped'] = false;

                        if (isset($fieldData['options']['locale'])) {
                            $fieldData['options']['locale'] = $localeData->getAlias();
                        }
                        $fieldData['options']['disabled'] = $this->isDisabled(
                            $localeData->getAlias(),
                            $fieldData['options']
                        );
                        if ($localeData->getAlias() != $this->localeManager->getDefaultLocaleAlias()) {
                            //убираем обязательность полей для языков
                            $fieldData['options']['required'] = false;

                            if (!empty($fieldData['options']['inner_constraints'])) {
                                foreach ($fieldData['options']['inner_constraints'] as $key => $value) {
                                    if ($value instanceof FileNotBlank) {
                                        unset($fieldData['options']['inner_constraints'][$key]);
                                    }
                                }
                            }
                        }
                    }
                    unset($fieldData['options']['translatable']);
                }

                $this->formMapper->add(
                    $fieldName,
                    $fieldData['type'],
                    $fieldData['options'],
                    $fieldData['fieldDescriptionOptions']
                );
            }

            $this->formMapper->end();
            $firstTab = false;
        }

        return $this->formMapper;
    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * Блокировка полей на уровне
     * ограничений пользователя админки
     * @param $locale
     * @param array $options
     *
     * @return bool
     */
    protected function isDisabled($locale, array $options)
    {
        $result = false;
        if (array_key_exists('disabled', $options)) {
            $result = $options['disabled'];
        }

        if (false == $result) {
            $result = $this->isDisabledByAdministratorLocales($locale);
        }

        return $result;
    }

    /**
     * @param $locale
     * @return bool
     */
    protected function isDisabledByAdministratorLocales($locale)
    {
        $result = false;
        $administrator = $this->getTokenStorage()->getToken()->getUser();
        if ($administrator instanceof Administrator) {
            $result = $administrator->isDisabledByAdministratorLocales($locale);
        }

        return $result;
    }
}

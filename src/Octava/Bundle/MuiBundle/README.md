# Модуль переводов

## Загрузка переводов в БД из коммандной строки

Выполняется коммандой

```bash
   app/console translation:update ru OctavaMuiBundle --force --output-format=zdb
```

Где `ru` - локаль, `OctavaMuiBundle` - бандл откуда загружать переводы

После выполнения комманды будут собраны переводы из всех файлов для соответсвующей локали и бандла объединены в единый словарь
и сохранены в БД. Если в БД не было записи с соответсвующем переводом (н-р метка лежала в xlf файле), то будет создана новая, если была
то обновится.

## Очистить кэш переводов

```bash
   app/console translation:cache:clear
```

## Загрузить переводы из файлов бандлов в БД

При загрузке система сначала ищет переводы в папке `app/Resources/translations`, потом в директориях бандлов
если перевод есть в `app/Resources/translations`, то вибирается он для загрузки в БД. Если в БД уже существовал перевод,
то он обновляться не будет.


```bash
   app/console translation:db:update
```

## Выгрузить переводы из БД в файлы
Выгружает переводы из БД в директорию `app/Resources/translations` в формате xlf

```bash
   app/console translation:db:dump
```

## Остальное

После сохранения измененией через интерфейс кэш сбрасывается автоматически

# Мультиызычные вкладки в формах админки

Для реализации используется сервис `robo_translation.form_mapper`. Данный сервис представляет собой обёртку над классом Сонаты FormMapper
объект которого передаётся в метод `configureFormFields()` класса Admin.

## Шаги по созданию мультиязычных вкладок:
1\. Подключаем сервис `robo_translation.form_mapper` зависимостью к сервису нужного объекта админки через сеттер:

```yml
#YourBundle/Resources/config/services.yml

your_bundle.admin.items:
    ...
    calls:
        - [ setTranslationMapper, [@robo_translation.form_mapper]]
        ...
```

2\. Добавляем сеттер в класс админки:

```php
namespace YourBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Robo\MuiBundle\Form\TranslationMapper;

class MyAdmin extends Admin
{
    protected $translationMapper;

    public function setTranslationMapper(TranslationMapper $translationMapper)
    {
        $this->translationMapper = $translationMapper;
    }
	...
}
```

3\. Применяем передаваемый сервис для создания формы в методе `configureFormFields()`

```php
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->translationMapper
            ->setFormMapper($formMapper)
            ->with()
                ->add('field1', 'text', array('translatable' => true, 'label' => 'Field1', 'required' => false))
                ->add('field2', 'textarea', array('translatable' => true, 'label' => 'Field2'))
                ->add('field3', 'datetime')
            ->end()
        ;
    }
```
В результате будет выведена форма состоящая из языковых вкладок на каждой из которых будет 2 поля -
`field1` с типом text и `field2` с типом textarea. Поле `field3` будет присутствовать только на первой языковой вкладке
и его значение не будет мультиязычным. Немультиязыные поля удобно располагать на первой вкладке в ситуации когда этих
полей мало и выделять для них отдельную вкладку нет смысла.

## Другие примеры
1\. Если форма состоит не только из мультиязычных вкладок, то все эти вкладки можно задать с помощью базового объекта
маппера формы передаваемого с метод. Например:

```php
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->translationMapper
            ->setFormMapper($formMapper)
            ->with()
                ->add('field1', 'text', array('translatable' => true, 'label' => 'Field1', 'required' => false))
                ->add('field2', 'datetime')
            ->end()
        ;

        $formMapper
			->wdth('Some tab')
				->add('field3')
			->end()
		;
    }
```
Результатом будет добавление вкладки `Some tab` в полем `field3` поле языковых вкладок.

2\. Укороченный пример предыдущего варианта:

```php
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->translationMapper
            ->setFormMapper($formMapper)
            ->with()
                ->add('field1', 'text', array('translatable' => true, 'label' => 'Field1', 'required' => false))
                ->add('field2', 'datetime')
            ->end()
        	->wdth('Some tab')
        		->add('field3')
        	->end()
        ;
    }
```
Таким образом, можно переходить к заданию остальных вкладок сразу после выполнения метода `end()` класса `TranslationMapper`
Это реализуется за счёт возврата из метода `end()` изначатьного объекта маппера формы.

3\. Задание вкладок до языковых:

```php
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->wdth('Some first tab')
                ->add('field3')
            ->end()
        ;

        $this->translationMapper
            ->setFormMapper($formMapper)
            ->with()
                ->add('field1', 'text', array('translatable' => true, 'label' => 'Field1', 'required' => false))
                ->add('field2', 'datetime')
            ->end()
        ;
    }
```

4\. Добавление произвольного немультиязычного поля на языковую вкладку после их конфигурирования:

```php
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->translationMapper
            ->setFormMapper($formMapper)
            ->with()
                ->add('field1', 'text', array('translatable' => true, 'label' => 'Field1', 'required' => false))
                ->add('field2', 'datetime')
            ->end()
        ;

         $formMapper
			->wdth('English')
				->add('field3')
			->end()
		;
    }
```
В примере `English` - это название языка определённое в справочнике языков.
В результате будет добавлено поле field3 на вкладку английского языка, если он присутствует для редаутирования.
В противном случае будет добавлена новая вкладка English с этим полем.
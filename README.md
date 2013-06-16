HtmlShortcode
=============

Module for Zend Framework 2 that allows render view helpers using HTML-code.
You can use this module with editors like TinyMCE and CKEditor.

## Installation

```sh
php composer.phar require jhekasoft/html-shortcode:dev-master
```
In `application.config.php` add the following key to your `modules`:

```php
'modules' => array(
    //...
    'HtmlShortcode',
),
```

## Usage

At controller:

```php
//...
use HMShortCode\Filter\ShortCodeFilter;

class IndexController
{
    public function showAction()
    {
        //...

        $shortCodeFilter = new ShortCodeFilter();
        $shortCodeFilter->setServiceLocator($this->getServiceLocator());

        $item->text = $shortCodeFilter->filter($item->text);

        return array(
            'item' => $item,
        );
    }
}
```

At entity:

```php
//...
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use HtmlShortcode\Filter\ShortcodeFilter;

class Pages implements InputFilterAwareInterface, ServiceLocatorAwareInterface
{
    public function exchangeArray($data)
    {
        $this->text = (isset($data['text'])) ? $data['text'] : null;

        // ShortCode filter
        $shortcodeFilter = new ShortcodeFilter();
        $shortcodeFilter->setServiceLocator($this->getServiceLocator());
        $this->filtered_text = $shortcodeFilter->filter($this->text);
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
```

On bootstrap (except edit action):

```php
//...
public function onBootstrap($e)
{
    $app = $e->getApplication();
    $em = $app->getEventManager();

    $em->attach(\Zend\Mvc\MvcEvent::EVENT_ROUTE, function($e) {
        $match = $e->getRouteMatch();
        $action = $match->getParam('action');

        if ('edit' != $action) {
            $sm = $e->getApplication()->getServiceManager();
            $view = $sm->get('ViewRenderer');
            $filters = $view->getFilterChain();
            $widgetFilter = new ShortCodeFilter();
            $widgetFilter->setServiceLocator($sm);
            $filters->attach($widgetFilter, 50);
            $view->setFilterChain($filters);
        }
    });
}
```

## HTML-code

Samples:

```php
<span class="htmlshortcode" data-helper="soundBlock" data-params="[value1]"></div>

<div class="htmlshortcode" data-helper="soundBlock" data-params="[value2]"></div>

<span class="htmlshortcode" data-helper="soundBlock" data-params='[value3][{"size":"small"}]'></span>
```

The examples above are equivalent this php-code in view-script:

```php
echo $this->soundBlock('value1');

echo $this->soundBlock('value2');

echo $this->soundBlock('value3', array("size" => "small"));
),
```


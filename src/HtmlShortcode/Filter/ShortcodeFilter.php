<?php

namespace HtmlShortcode\Filter;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Filter\AbstractFilter;
use Zend\Json\Json;
use Zend\Dom\Query;

class ShortcodeFilter extends AbstractFilter implements ServiceLocatorAwareInterface
{

    protected $serviceLocator;

    public function filter($value)
    {
        $dom = new Query($value);
        $results = $dom->execute('div.htmlshortcode, span.htmlshortcode');
        foreach ($results as $result) {
            $shortcode = $result->ownerDocument->saveHTML($result);

            $convertedValue = $this->convertShortcodeToViewHelper($result);

            if (empty($convertedValue)) {
                return $value;
            }

            $value = str_replace($shortcode, $convertedValue, $value);
        }

        $filteredValue = $value;

        return $filteredValue;
    }

    protected function convertShortcodeToViewHelper(\DOMElement $element)
    {
        if(!$element->hasAttribute('data-helper')) {
            return NULL;
        }

        // Helper method
        $helper = $element->getAttribute('data-helper');

        // Parameters
        $parameters = array();
        if($element->hasAttribute('data-params')) {
            $parts = explode("[", $element->getAttribute('data-params'));
            unset($parts[0]);
            if (count($parts) > 0) {
                foreach ($parts as $part) {
                    $data = substr_replace($part, "", -1);
                    if ('{' == substr($data, 0, 1)) {
                        $parameters[] = Json::decode(html_entity_decode($data), Json::TYPE_ARRAY);
                    } else {
                        $parameters[] = $data;
                    }
                }
            }
        }

        $view = $this->serviceLocator->get('ViewRenderer');
        $converted = call_user_func_array(array($view, $helper), $parameters);

        return $converted;
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return CustomHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get the service locator.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

}

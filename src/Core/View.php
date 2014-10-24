<?php
namespace Sinergi\Core;

use Router\Response;
use Router\Request;

abstract class View
{
    /**
     * @return Request
     */
    abstract public function getRequest();

    /**
     * @return Response
     */
    abstract public function getResponse();

    /**
     * @return Twig
     */
    abstract public function getTwig();

    /**
     * @return ContainerInterface
     */
    abstract public function getContainer();

    use ComponentRegistryTrait;

    /**
     * @param null|string $body
     * @return Response|string
     * @deprecated
     */
    public function body($body = null)
    {
        return $this->getResponse()->body($body);
    }

    /**
     * @param mixed $object
     * @param string $jsonp_prefix
     * @param null|array $mask
     * @return void
     * @deprecated
     */
    public function json($object, $jsonp_prefix = null, array $mask = null)
    {
        $json = json_encode($object);
        $object = json_decode($json, true);
        if (null !== $mask) {
            $retval = [];
            foreach ($mask as $oldKey => $key) {
                if (is_string($oldKey)) {
                    $retval[$key] = isset($object[$oldKey]) ? $object[$oldKey] : null;
                } else {
                    $retval[$key] = isset($object[$key]) ? $object[$key] : null;
                }
            }
            $object = $retval;
        }
        $this->getResponse()->json($object, $jsonp_prefix);
    }

    /**
     * Returns the cookies collection
     *
     * @access public
     * @return \Klein\DataCollection\ResponseCookieDataCollection
     * @deprecated
     */
    public function cookies()
    {
        return $this->getResponse()->cookies();
    }

    /**
     * @param string $url
     * @param int $code
     * @return Response
     * @deprecated
     */
    public function redirect($url, $code = 302)
    {
        return $this->getResponse()->redirect($url, $code);
    }

    /**
     * @param int $code
     * @return int|Response
     * @deprecated
     */
    public function code($code = null)
    {
        return $this->getResponse()->code($code);
    }

    /**
     * @param string $name
     * @param array $context
     * @return string
     * @deprecated
     */
    public function render($name, array $context = [])
    {
        return $this->getTwig()->getEnvironment()->render(
            $name,
            $context
        );
    }

    /**
     * @param $key
     * @return null|mixed
     * @deprecated
     */
    public function getParam($key)
    {
        $params = $this->getParams([$key]);
        if (isset($params[$key])) {
            return $params[$key];
        }
        return null;
    }

    /**
     * @param array $mask
     * @return array
     * @deprecated
     */
    public function getParams(array $mask = null)
    {
        $params = array_merge(
            $this->getRequest()->paramsGet()->all(),
            $this->getRequest()->paramsPost()->all()
        );
        if (!count($params)) {
            $params = $this->getRequest()->body();
            if (is_string($params)) {
                $bodyParams = json_decode($params, true);
                if (!is_array($bodyParams) && is_string($params)) {
                    preg_match_all('/[ &]?([^ =&]+)=["|\']?([^"\',&]+)["|\']?/', $params, $matches);
                    if (isset($matches[1]) && isset($matches[2]) && count($matches[1])) {
                        $bodyParams = [];
                        foreach ($matches[1] as $count => $key) {
                            $bodyParams[$key] = urldecode(isset($matches[2][$count]) ? $matches[2][$count] : null);
                        }
                    }
                }
                if (is_array($bodyParams)) {
                    $params = $bodyParams;
                }
            }
        }

        $retval = $params;
        if (null !== $mask) {
            $retval = [];
            foreach ($mask as $oldKey => $key) {
                if (is_string($oldKey)) {
                    $retval[$key] = isset($params[$oldKey]) ? $params[$oldKey] : null;
                } else {
                    $retval[$key] = isset($params[$key]) ? $params[$key] : null;
                }
            }
        }
        return $retval;
    }
}

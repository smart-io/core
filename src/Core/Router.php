<?php
namespace Sinergi\Core;

use Sinergi\Core\Registry\ComponentRegistryTrait;

abstract class Router implements RouterInterface
{
    use ComponentRegistryTrait;

    /**
     * @param $key
     * @return null|mixed
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
     */
    public function getParams(array $mask = null)
    {
        $params = $this->getRequest()->paramsPost()->all();
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

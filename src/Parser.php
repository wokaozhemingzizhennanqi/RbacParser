<?php
namespace Rbac\SDK;

class Parser
{
    //资源类型的规则枚举
    const TYPE_RULE_BOOL = 'bool';
    const TYPE_RULE_EQUAL = 'equal';
    const TYPE_RULE_CRUD = 'crud';

    //CRUD操作
    const CRUD_FUNCTION_CREATE = 'create';
    const CRUD_FUNCTION_READ = 'read';
    const CRUD_FUNCTION_UPDATE = 'update';
    const CRUD_FUNCTION_DELETE = 'delete';

    //CRUD范围
    const CRUD_SCOPE_ALL = 'all';
    const CRUD_SCOPE_SELF = 'self';
    const CRUD_SCOPE_WITH_SUBORDINATE = 'subordinate';
    const TYPE_BOOL = 'display';
    const TYPE_EQUAL = 'route';
    const TYPE_CRUD = 'crud';

    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }



    public function getBool($key)
    {
        return self::checkBool($this->data, self::TYPE_BOOL,$key);
    }

    public function getRoute($key)
    {
        return self::checkEqual($this->data, self::TYPE_EQUAL, $key);
    }

    public function getReadCrud($key)
    {
        return self::checkCRUD($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_READ);
    }

    public function getReadCrudIds($key)
    {
        return self::getCRUDParams($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_READ);
    }

    public function getAddCrud($key)
    {
        return self::checkCRUD($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_CREATE);
    }

    public function getAddCrudIds($key)
    {
        return self::getCRUDParams($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_CREATE);
    }

    public function getUpdateCrud($key)
    {
        return self::checkCRUD($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_UPDATE);
    }

    public function getUpdateCrudIds($key)
    {
        return self::getCRUDParams($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_UPDATE);
    }

    public function getDeleteCrud($key)
    {
        return self::checkCRUD($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_DELETE);
    }

    public function getDeleteCrudIds($key)
    {
        return self::getCRUDParams($this->data, self::TYPE_CRUD, $key, self::CRUD_FUNCTION_DELETE);
    }


    public function getCRUDParamsThrowExceptionNoPermission($key,$type =  self::TYPE_CRUD)
    {
        $message = '';
        try {
            self::getCRUDParams($this->data, $type, $key, self::CRUD_FUNCTION_READ);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return $message;
    }
    /**
     * @param array $data
     * @param string $typeName
     * @param string $key
     * @return bool
     */
    public static function checkBool($data, $typeName, $key)
    {
        $assignments = $data['assignments'];
        foreach ($assignments as $assignment) {
            if ($assignment['type_name'] == $typeName) {
                if ($assignment['rule'] == self::TYPE_RULE_BOOL) {
                    return isset($assignment['permissions'][$key]) && $assignment['permissions'][$key] === true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $data 权限接口返回值
     * @param string $typeName
     * @param string $key
     * @return bool
     */
    public static function checkEqual($data, $typeName, $key)
    {
        $assignments = $data['assignments'];
        foreach ($assignments as $assignment) {
            if ($assignment['type_name'] == $typeName) {
                if ($assignment['rule'] == self::TYPE_RULE_EQUAL) {
                    return in_array($key, $assignment['permissions']);
                }
            }
        }

        return false;
    }

    /**
     * @param array $data 权限接口返回值
     * @param string $typeName
     * @param string $key
     * @param string $function
     * @return bool|array
     */
    public static function checkCRUD($data, $typeName, $key, $function)
    {
        $assignments = $data['assignments'];
        foreach ($assignments as $assignment) {
            if ($assignment['type_name'] == $typeName && $assignment['rule'] == self::TYPE_RULE_CRUD) {
                if (isset($assignment['permissions'][$key]['functions'])
                    && in_array($function, $assignment['permissions'][$key]['functions'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $data 权限接口返回值
     * @param string $typeName
     * @param string $key
     * @param string $function
     * @return bool|array
     * @throws \Exception
     */
    public static function getCRUDParams($data, $typeName, $key, $function)
    {
        $assignments = $data['assignments'];
        foreach ($assignments as $assignment) {
            if ($assignment['type_name'] == $typeName && $assignment['rule'] == self::TYPE_RULE_CRUD) {
                if (isset($assignment['permissions'][$key]['functions'])
                    && in_array($function, $assignment['permissions'][$key]['functions'])) {
                    if (isset($assignment['permissions'][$key]['scopes'])) {
                        if (in_array(self::CRUD_SCOPE_ALL, (array)$assignment['permissions'][$key]['scopes'])) {
                            return true;
                        } elseif (in_array(self::CRUD_SCOPE_WITH_SUBORDINATE, (array)$assignment['permissions'][$key]['scopes'])) {
                            return $data['subordinates'];
                        }
                    }

                    return $data['self'];
                }
            }
        }

        throw new \Exception('没有权限');
    }
}
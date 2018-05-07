<?php
namespace Rbac\SDK\Test;

use PHPUnit\Framework\TestCase;
use Rbac\SDK\Parser;

class ParserTest extends TestCase
{
    private $data = [
        'assignments' => [
            [
                'name' => '路由',
                'rule' => 'equal',
                'type_id' => 2,
                'type_name' => 'unique.route',
                'permissions' => [
                    "/admin/6",
                    "/admin",
                    "/admin/1",
                ]
            ],
            [
                "name" => "DIV",
                "rule" => "bool",
                "type_id" => 3,
                "type_name" => "div.show",
                "permissions" => [
                    "rule_5" => false,
                    "rule_11" => true,
                    "rule_4" => true
                ]
            ],
            [
                "name" => "CRUD",
                "rule" => "crud",
                "type_id" => 4,
                "type_name" => "unique.crud",
                "permissions" => [
                    "campaign3" => [
                        "scopes" => [
                            "all"
                        ],
                        "functions" => [
                            "create"
                        ]
                    ],
                    "campaign2" => [
                        "functions" => [
                            "create",
                            "read",
                            "update"
                        ],
                        "scopes" => [
                            "subordinate"
                        ]
                    ],
                    "campaign5" => [
                        "functions" => [
                            "create",
                        ],
                        "scopes" => [
                            "self"
                        ]
                    ],
                ]
            ]
        ],
        "self" => [
            1
        ],
        "subordinates" => [
            1, 2, 3
        ]
    ];

    public function testCheckBoolReturnTrue()
    {
        $this->assertTrue(Parser::checkBool($this->data, 'div.show', 'rule_11'));
    }

    public function testCheckBoolReturnFalseForNoSettingValue()
    {
        $this->assertFalse(Parser::checkBool($this->data, 'div.show', 'rule_111'));
    }

    public function testCheckBoolReturnFalseForSettingValue()
    {
        $this->assertFalse(Parser::checkBool($this->data, 'div.show', 'rule_5'));
    }

    public function testCheckEqualReturnTrue()
    {
        $this->assertTrue(Parser::checkEqual($this->data, 'unique.route', '/admin/6'));
    }

    public function testCheckEqualReturnFalse()
    {
        $this->assertFalse(Parser::checkEqual($this->data, 'unique.route', '/admin/66'));
    }

    public function testCheckCRUDReturnTrue()
    {
        $this->assertTrue(Parser::checkCRUD($this->data, 'unique.crud', 'campaign3', Parser::CRUD_FUNCTION_CREATE));
    }

    public function testCheckCRUDReturnFalse()
    {
        $this->assertFalse(Parser::checkCRUD($this->data, 'unique.crud', 'campaign3', Parser::CRUD_FUNCTION_READ));
    }

    public function testGetCRUDParamsReturnAllIds()
    {
        $this->assertTrue(Parser::getCRUDParams($this->data, 'unique.crud', 'campaign3', Parser::CRUD_FUNCTION_CREATE));
    }

    public function testGetCRUDParamsReturnSelfIds()
    {
        $this->assertArraySubset([1], Parser::getCRUDParams($this->data, 'unique.crud', 'campaign5', Parser::CRUD_FUNCTION_CREATE));
    }

    public function testGetCRUDParamsReturnSubordinate()
    {
        $this->assertArraySubset([1,2,3], Parser::getCRUDParams($this->data, 'unique.crud', 'campaign2', Parser::CRUD_FUNCTION_CREATE));
    }

    public function testGetCRUDParamsThrowExceptionNoPermission()
    {
        $message = '';
        try {
            Parser::getCRUDParams($this->data, 'unique.crud', 'campaign5', Parser::CRUD_FUNCTION_READ);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertEquals('没有权限', $message);
    }

}


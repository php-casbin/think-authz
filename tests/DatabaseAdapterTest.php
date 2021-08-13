<?php

namespace tauthz\tests;

use tauthz\facade\Enforcer;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;

class DatabaseAdapterTest extends TestCase
{
    public function testEnforce()
    {
        $this->testing(function () {

            $this->assertTrue(Enforcer::enforce('alice', 'data1', 'read'));

            $this->assertFalse(Enforcer::enforce('bob', 'data1', 'read'));
            $this->assertTrue(Enforcer::enforce('bob', 'data2', 'write'));

            $this->assertTrue(Enforcer::enforce('alice', 'data2', 'read'));
            $this->assertTrue(Enforcer::enforce('alice', 'data2', 'write'));

        });
    }

    public function testAddPolicy()
    {
        $this->testing(function () {
            $this->assertFalse(Enforcer::enforce('eve', 'data3', 'read'));
            Enforcer::addPermissionForUser('eve', 'data3', 'read');
            $this->assertTrue(Enforcer::enforce('eve', 'data3', 'read'));
        });
    }

    public function testAddPolicies()
    {
        $this->testing(function () {
            $policies = [
                ['u1', 'd1', 'read'],
                ['u2', 'd2', 'read'],
                ['u3', 'd3', 'read'],
            ];
            Enforcer::clearPolicy();
            $this->initTable();
            $this->assertEquals([], Enforcer::getPolicy());
            Enforcer::addPolicies($policies);
            $this->assertEquals($policies, Enforcer::getPolicy());
        });
    }

    public function testSavePolicy()
    {
        $this->testing(function () {
            $this->assertFalse(Enforcer::enforce('alice', 'data4', 'read'));

            $model = Enforcer::getModel();
            $model->clearPolicy();
            $model->addPolicy('p', 'p', ['alice', 'data4', 'read']);

            $adapter = Enforcer::getAdapter();
            $adapter->savePolicy($model);
            $this->assertTrue(Enforcer::enforce('alice', 'data4', 'read'));
        });
    }

    public function testRemovePolicy()
    {
        $this->testing(function () {
            $this->assertFalse(Enforcer::enforce('alice', 'data5', 'read'));

            Enforcer::addPermissionForUser('alice', 'data5', 'read');
            $this->assertTrue(Enforcer::enforce('alice', 'data5', 'read'));

            Enforcer::deletePermissionForUser('alice', 'data5', 'read');
            $this->assertFalse(Enforcer::enforce('alice', 'data5', 'read'));
        });
    }

    public function testRemovePolicies()
    {
        $this->testing(function () {
            $this->assertEquals([
                ['alice', 'data1', 'read'],
                ['bob', 'data2', 'write'],
                ['data2_admin', 'data2', 'read'],
                ['data2_admin', 'data2', 'write'],
            ], Enforcer::getPolicy());
    
            Enforcer::removePolicies([
                ['data2_admin', 'data2', 'read'],
                ['data2_admin', 'data2', 'write'],
            ]);
    
            $this->assertEquals([
                ['alice', 'data1', 'read'],
                ['bob', 'data2', 'write']
            ], Enforcer::getPolicy());
        });
    }

    public function testRemoveFilteredPolicy()
    {
        $this->testing(function () {
            $this->assertTrue(Enforcer::enforce('alice', 'data1', 'read'));
            Enforcer::removeFilteredPolicy(1, 'data1');
            $this->assertFalse(Enforcer::enforce('alice', 'data1', 'read'));
            $this->assertTrue(Enforcer::enforce('bob', 'data2', 'write'));
            $this->assertTrue(Enforcer::enforce('alice', 'data2', 'read'));
            $this->assertTrue(Enforcer::enforce('alice', 'data2', 'write'));
            Enforcer::removeFilteredPolicy(1, 'data2', 'read');
            $this->assertTrue(Enforcer::enforce('bob', 'data2', 'write'));
            $this->assertFalse(Enforcer::enforce('alice', 'data2', 'read'));
            $this->assertTrue(Enforcer::enforce('alice', 'data2', 'write'));
            Enforcer::removeFilteredPolicy(2, 'write');
            $this->assertFalse(Enforcer::enforce('bob', 'data2', 'write'));
            $this->assertFalse(Enforcer::enforce('alice', 'data2', 'write'));
        });
    }

    public function testUpdatePolicy()
    {
        $this->testing(function () {
            $this->assertEquals([
                ['alice', 'data1', 'read'],
                ['bob', 'data2', 'write'],
                ['data2_admin', 'data2', 'read'],
                ['data2_admin', 'data2', 'write'],
            ], Enforcer::getPolicy());
    
            Enforcer::updatePolicy(
                ['alice', 'data1', 'read'],
                ['alice', 'data1', 'write']
            );
    
            Enforcer::updatePolicy(
                ['bob', 'data2', 'write'],
                ['bob', 'data2', 'read']
            );
    
            $this->assertEquals([
                ['alice', 'data1', 'write'],
                ['bob', 'data2', 'read'],
                ['data2_admin', 'data2', 'read'],
                ['data2_admin', 'data2', 'write'],
            ], Enforcer::getPolicy());
        });
    }

    public function testUpdatePolicies()
    {
        $this->testing(function () {
            $this->assertEquals([
                ['alice', 'data1', 'read'],
                ['bob', 'data2', 'write'],
                ['data2_admin', 'data2', 'read'],
                ['data2_admin', 'data2', 'write'],
            ], Enforcer::getPolicy());
    
            $oldPolicies = [
                ['alice', 'data1', 'read'],
                ['bob', 'data2', 'write']
            ];
            $newPolicies = [
                ['alice', 'data1', 'write'],
                ['bob', 'data2', 'read']
            ];
    
            Enforcer::updatePolicies($oldPolicies, $newPolicies);
    
            $this->assertEquals([
                ['alice', 'data1', 'write'],
                ['bob', 'data2', 'read'],
                ['data2_admin', 'data2', 'read'],
                ['data2_admin', 'data2', 'write'],
            ], Enforcer::getPolicy());
        });
    }

    public function testLoadFilteredPolicy()
    {
        $this->testing(function () {
            $this->initTable();
            Enforcer::clearPolicy();
            $adapter = Enforcer::getAdapter();
            $adapter->setFiltered(true);
            $this->assertEquals([], Enforcer::getPolicy());

            // invalid filter type
            try {
                $filter = ['alice', 'data1', 'read'];
                Enforcer::loadFilteredPolicy($filter);
                $e = InvalidFilterTypeException::class;
                $this->fail("Expected exception $e not thrown");
            } catch (InvalidFilterTypeException $e) {
                $this->assertEquals("invalid filter type", $e->getMessage());
            }

            // string
            $filter = "v0 = 'bob'";
            Enforcer::loadFilteredPolicy($filter);
            $this->assertEquals([
                ['bob', 'data2', 'write']
            ], Enforcer::getPolicy());
            
            // Filter
            $filter = new Filter(['v2'], ['read']);
            Enforcer::loadFilteredPolicy($filter);
            $this->assertEquals([
                ['alice', 'data1', 'read'],
                ['data2_admin', 'data2', 'read'],
            ], Enforcer::getPolicy());

            // Closure
            Enforcer::loadFilteredPolicy(function ($query) {
                $query->where('v1', 'data1');
            });

            $this->assertEquals([
                ['alice', 'data1', 'read'],
            ], Enforcer::getPolicy());
            });
    }
}

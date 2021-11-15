<?php

namespace SilverStripe\CKANRegistry\Tests\Model;

use InvalidArgumentException;
use SilverStripe\CKANRegistry\Model\ResourceField;
use SilverStripe\CKANRegistry\Model\ResourceFilter;
use SilverStripe\Dev\SapphireTest;

class ResourceFilterTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testForTemplateThrowsExceptionWithNonFormFieldType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SilverStripe\Control\HTTPResponse is not a FormField');
        $filter = new ResourceFilterTest\InvalidResourceFilter();
        $filter->forTemplate();
    }

    public function testGetType()
    {
        $filter = new ResourceFilter();
        $this->assertSame('Text', $filter->getType());
    }

    public function testGetCMSFields()
    {
        $filter = new ResourceFilter();
        $fields = $filter->getCMSFields();

        $this->assertNull($fields->dataFieldByName('FilterForID'), 'FilterForID should be removed');
    }

    public function testGetColumnsNoFields()
    {
        $filter = new ResourceFilter();
        $filter->AllColumns = false;

        $this->assertEmpty($filter->getColumns(), 'Should return an empty string without fields');
    }

    public function testGetColumnsOneField()
    {
        $filter = new ResourceFilter();
        $filter->AllColumns = false;
        $field = new ResourceField();
        $field->ReadableLabel = 'My field';
        $filter->FilterFields()->add($field);

        $this->assertSame('My field', $filter->getColumns(), 'Should return the single field name');
    }

    public function testGetColumnsMultipleFields()
    {
        $filter = new ResourceFilter();
        $filter->AllColumns = false;

        $field = new ResourceField();
        $filter->FilterFields()->add($field);

        $field2 = new ResourceField();
        $filter->FilterFields()->add($field2);

        $this->assertStringContainsString(
            'Multiple columns',
            $filter->getColumns(),
            'Should return "multiple columns"'
        );
    }

    public function testGetColumnsAllColumns()
    {
        $filter = new ResourceFilter();
        $filter->AllColumns = true;

        $this->assertStringContainsString('All columns', $filter->getColumns(), 'Should return "all columns"');
    }

    public function testDefaultValues()
    {
        $filter = new ResourceFilter();
        $filter->write();

        $this->assertTrue($filter->AllColumns, 'AllColumns should be enabled by default');
    }

    public function testTitleShouldBeFilterLabel()
    {
        $field = new ResourceFilter();
        $field->FilterLabel = 'My filter name';
        $this->assertSame('My filter name', $field->getTitle());
    }
}

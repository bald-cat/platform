<?php

declare(strict_types=1);

namespace Orchid\Tests\Unit\Screen\Fields;

use Illuminate\Support\Facades\Session;
use Orchid\Screen\Fields\Input;
use Orchid\Tests\Unit\Screen\TestFieldsUnitCase;
use Throwable;

class InputTest extends TestFieldsUnitCase
{
    /**
     * @throws Throwable
     */
    public function test_show_hr(): void
    {
        $input = Input::make('inputFieldName');
        $hr = '<div class="line line-dashed border-bottom my-3"></div>';

        $this->assertStringNotContainsString($hr, self::renderField($input));

        $input->hr();

        $this->assertStringContainsString($hr, self::renderField($input));
    }

    public function test_array_mask(): void
    {
        $input = Input::make('price')
            ->mask([
                'alias'          => 'currency',
                'prefix'         => ' ',
                'groupSeparator' => ' ',
                'digitsOptional' => true,
            ]);

        $view = self::minifyRenderField($input);

        $this->assertStringContainsString('currency', $view);
    }

    public function test_string_mask(): void
    {
        $input = Input::make('phone')
            ->mask('(999) 999-9999');

        $view = self::minifyRenderField($input);

        $this->assertStringContainsString('(999) 999-9999', $view);
    }

    public function test_object_to_sting(): void
    {
        $input = Input::make('name')
            ->title('What is your name?');

        $this->assertStringContainsString('What is your name?', (string) $input);
    }

    public function test_data_attributes(): void
    {
        $input = (string) Input::make('name')
            ->set('data-name', 'Alexandr Chernyaev')
            ->set('data-location', 'Russia')
            ->set('data-hello', 'world!');

        $this->assertStringContainsString('data-name="Alexandr Chernyaev"', $input);
        $this->assertStringContainsString('data-location="Russia"', $input);
        $this->assertStringContainsString('data-hello="world!"', $input);
    }

    public function test_escape_attributes(): void
    {
        $input = (string) Input::make('name')->value('valueQuote"');

        $this->assertStringContainsString('value="valueQuote&quot;"', $input);
    }

    public function test_remove_boolean_attributes(): void
    {
        $input = (string) Input::make('name')->required(false);

        $this->assertStringNotContainsString('required', $input);
    }

    public function test_data_list_attribute(): void
    {
        $input = (string) Input::make('browser')->datalist([
            'Opera', 'Edge', 'Firefox',
            'Chrome', 'Safari',
        ]);

        $this->assertStringContainsString('Safari', $input);
        $this->assertStringContainsString('list="datalist-browser"', $input);
        $this->assertStringContainsString('<datalist id="datalist-browser"', $input);

        $input = (string) Input::make('browser')->datalist([]);

        $this->assertStringNotContainsString('list="datalist-browser"', $input);
        $this->assertStringNotContainsString('<datalist id="datalist-browser"', $input);
    }

    public function test_without_form_type()
    {
        $input = Input::make('inputFieldName')
            ->title('Lorem ipsum dolor sit amet')
            ->withoutFormType();

        $this->assertStringContainsString('Lorem ipsum dolor sit amet', self::renderField($input));
        $this->assertStringNotContainsString('</label>', self::renderField($input));
    }

    public function test_add_min_length_attribute(): void
    {
        $input = (string) Input::make('name')->minlength(3);

        $this->assertStringContainsString('minlength="3"', $input);
    }

    public function test_long_numeric_value(): void
    {
        Session::start();

        Session::put('_old_input', [
            'numeric' => '1234567890123456789012345678901234567890',
        ]);

        request()->setLaravelSession(session());

        $input = Input::make('numeric')->getOldValue();

        $this->assertEquals('1234567890123456789012345678901234567890', $input);
    }

    public function test_medium_long_numeric_value(): void
    {
        Session::start();

        Session::put('_old_input', [
            'numeric' => '66666666666666666666',
        ]);

        request()->setLaravelSession(session());

        $input = Input::make('numeric')->getOldValue();

        $this->assertEquals('66666666666666666666', $input);
    }
}

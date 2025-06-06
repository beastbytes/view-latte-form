<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Form\Tests;

use BeastBytes\View\Latte\Form\FormExtension;
use BeastBytes\View\Latte\Form\Tests\Support\TestForm;
use Generator;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Validator;

class FormFieldTest extends TestCase
{
    private const ERROR_SUMMARY_FOOTER = 'Error Summary Footer';
    private const ERROR_SUMMARY_HEADER = 'Error Summary Header';
    private const LEGEND = 'Test Legend';

    private static Inflector $inflector;

    #[BeforeClass]
    public static function beforeClass(): void
    {
        parent::beforeClass();
        self::$inflector = new Inflector();
    }

    #[Test]
    #[DataProvider('buttonTagProvider')]
    public function button(string $tag, string $expected): void
    {
        $this->createButtonTemplate($tag);
        $html = $this->renderToString($tag);
        $this->assertSame($expected, $html);
    }

    #[Test]
    #[DataProvider('buttonGroupTagProvider')]
    public function buttonGroup(?bool $encode, string $expected): void
    {
        $tag = 'buttonGroup';
        $this->createButtonGroupTemplate($tag, $encode);
        $html = $this->renderToString($tag . (is_bool($encode) ? ($encode ? 'true' : 'false') : ''));
        $this->assertSame($expected, $html);
    }

    #[Test]
    #[DataProvider('fieldTagProvider')]
    public function field(string $tag, string $expected): void
    {
        $this->createFieldTemplate($tag);
        $html = $this->renderToString($tag, ['formModel' => new TestForm()]);
        $this->assertSame($expected, $html);
    }

    #[Test]
    #[DataProvider('errorSummaryProvider')]
    public function errorSummary(string $name, string $tag, string $expected): void
    {
        $formModel = new TestForm();
        $validator = new Validator();
        $validator->validate($formModel);

        $this->createErrorSummaryTemplate($name, $tag);
        $html = $this->renderToString($name, ['formModel' => $formModel]);
        $this->assertSame($expected, $html);

    }

    #[Test]
    #[DataProvider('fieldsetProvider')]
    public function fieldset(string $tag, string $expected): void
    {
        $this->createFieldsetTemplate();
        $html = $this->renderToString($tag, ['formModel' => new TestForm()]);
        $this->assertSame($expected, $html);
    }

    #[Test]
    #[DataProvider('optionsFieldTagProvider')]
    public function optionsField(string $tag, string $expected): void
    {
        $this->createOptionsFieldTemplate($tag);
        $html = $this->renderToString($tag, ['formModel' => new TestForm()]);
        $this->assertSame($expected, $html);
    }

    #[Test]
    #[DataProvider('tagProvider')]
    public function getTags(string $tag): void
    {
        $extension = new FormExtension();
        $tags = $extension->getTags();

        $this->assertIsArray($tags);
        $this->assertArrayHasKey(key: $tag, array: $tags);
    }

    public static function tagProvider(): Generator
    {
        foreach (self::buttonTagProvider() as $item) {
            yield [$item['tag']];
        }
        foreach (self::fieldTagProvider() as $item) {
            yield [$item['tag']];
        }
        foreach (self::optionsFieldTagProvider() as $item) {
            yield [$item['tag']];
        }
    }

    public static function buttonGroupTagProvider(): Generator
    {
        yield 'buttonGroup' => [
            'encode' => null,
            'expected' => <<<EXPECTED
<div>
<button type="reset" class="default">Reset</button>
<button type="button" class="more">Add</button>
<button type="submit" class="primary">Send</button>
</div>

EXPECTED,
        ];
        yield 'buttonGroupEncode' => [
            'encode' => true,
            'expected' => <<<EXPECTED
<div>
<button type="reset" class="default">Reset &amp; Clear</button>
<button type="button" class="more">Add</button>
<button type="submit" class="primary">Send</button>
</div>

EXPECTED,
        ];
        yield 'buttonGroupNoEncode' => [
            'encode' => false,
            'expected' => <<<EXPECTED
<div>
<button type="reset" class="default">Reset & Clear</button>
<button type="button" class="more">Add</button>
<button type="submit" class="primary">Send</button>
</div>

EXPECTED,
        ];
    }

    public static function buttonTagProvider(): Generator
    {
        yield 'button' => [
            'tag' => 'button',
            'expected' => <<<EXPECTED
<div>
<button type="button">Button</button>
</div>

EXPECTED,
        ];
        yield 'image' => [
            'tag' => 'image',
            'expected' => <<<EXPECTED
<div>
<input type="image" src="image@example.com">
</div>

EXPECTED,
        ];
        yield 'resetButton' => [
            'tag' => 'resetButton',
            'expected' => <<<EXPECTED
<div>
<button type="reset">Reset Button</button>
</div>

EXPECTED,
        ];
        yield 'reset' => [
            'tag' => 'reset',
            'expected' => <<<EXPECTED
<div>
<button type="reset">Reset</button>
</div>

EXPECTED,
        ];
        yield 'submitButton' => [
            'tag' => 'submitButton',
            'expected' => <<<EXPECTED
<div>
<button type="submit">Submit Button</button>
</div>

EXPECTED,
        ];
        yield 'submit' => [
            'tag' => 'submit',
            'expected' => <<<EXPECTED
<div>
<button type="submit">Submit</button>
</div>

EXPECTED,
        ];
    }

    public static function optionsFieldTagProvider(): Generator
    {
        yield 'checkboxList' => [
            'tag' => 'checkboxList',
            'expected' => <<<EXPECTED
<div>
<label>CheckboxList Field</label>
<div>
<label><input type="checkbox" name="TestForm[checkboxList][]" value="one"> One</label>
<label><input type="checkbox" name="TestForm[checkboxList][]" value="two"> Two</label>
<label><input type="checkbox" name="TestForm[checkboxList][]" value="three"> Three</label>
</div>
</div>

EXPECTED,
        ];
        yield 'radioList' => [
            'tag' => 'radioList',
            'expected' => <<<EXPECTED
<div>
<label>RadioList Field</label>
<div>
<label><input type="radio" name="TestForm[radioList]" value="one"> One</label>
<label><input type="radio" name="TestForm[radioList]" value="two"> Two</label>
<label><input type="radio" name="TestForm[radioList]" value="three"> Three</label>
</div>
</div>

EXPECTED,
        ];
        yield 'select' => [
            'tag' => 'select',
            'expected' => <<<EXPECTED
<div>
<label for="testform-select">Select Field</label>
<select id="testform-select" name="TestForm[select]">
<option value="one">One</option>
<option value="two">Two</option>
<option value="three">Three</option>
</select>
</div>

EXPECTED,
        ];
    }

    public static function fieldTagProvider(): Generator
    {
        yield 'checkbox' => [
            'tag' => 'checkbox',
            'expected' => <<<EXPECTED
<div>
<input type="hidden" name="TestForm[checkbox]" value="0"><label><input type="checkbox" id="testform-checkbox" name="TestForm[checkbox]" value="1" checked> Checkbox Field</label>
</div>

EXPECTED,
        ];
        yield 'date' => [
            'tag' => 'date',
            'expected' => <<<EXPECTED
<div>
<label for="testform-date">Date Field</label>
<input type="date" id="testform-date" name="TestForm[date]" value>
</div>

EXPECTED,
        ];
        yield 'dateTimeLocal' => [
            'tag' => 'dateTimeLocal',
            'expected' => <<<EXPECTED
<div>
<label for="testform-datetimelocal">DateTimeLocal Field</label>
<input type="datetime-local" id="testform-datetimelocal" name="TestForm[dateTimeLocal]" value>
</div>

EXPECTED,
        ];
        yield 'email' => [
            'tag' => 'email',
            'expected' => <<<EXPECTED
<div>
<label for="testform-email">Email Field</label>
<input type="email" id="testform-email" name="TestForm[email]" value>
</div>

EXPECTED,
        ];
        yield 'file' => [
            'tag' => 'file',
            'expected' => <<<EXPECTED
<div>
<label for="testform-file">File Field</label>
<input type="file" id="testform-file" name="TestForm[file]">
</div>

EXPECTED,
        ];
        yield 'hidden' => [
            'tag' => 'hidden',
            'expected' => <<<EXPECTED
<input type="hidden" id="testform-hidden" name="TestForm[hidden]" value>

EXPECTED,
        ];
        yield 'number' => [
            'tag' => 'number',
            'expected' => <<<EXPECTED
<div>
<label for="testform-number">Number Field</label>
<input type="number" id="testform-number" name="TestForm[number]" value>
</div>

EXPECTED,
        ];
        yield 'password' => [
            'tag' => 'password',
            'expected' => <<<EXPECTED
<div>
<label for="testform-password">Password Field</label>
<input type="password" id="testform-password" name="TestForm[password]" value>
</div>

EXPECTED,
        ];
        yield 'range' => [
            'tag' => 'range',
            'expected' => <<<EXPECTED
<div>
<label for="testform-range">Range Field</label>
<input type="range" id="testform-range" name="TestForm[range]" value>
</div>

EXPECTED,
        ];
        yield 'telephone' => [
            'tag' => 'telephone',
            'expected' => <<<EXPECTED
<div>
<label for="testform-telephone">Telephone Field</label>
<input type="tel" id="testform-telephone" name="TestForm[telephone]" value>
</div>

EXPECTED,
        ];
        yield 'text' => [
            'tag' => 'text',
            'expected' => <<<EXPECTED
<div>
<label for="testform-text">Text Field</label>
<input type="text" id="testform-text" name="TestForm[text]" value>
</div>

EXPECTED,
        ];
        yield 'textarea' => [
            'tag' => 'textarea',
            'expected' => <<<EXPECTED
<div>
<label for="testform-textarea">Textarea Field</label>
<textarea id="testform-textarea" name="TestForm[textarea]"></textarea>
</div>

EXPECTED,
        ];
        yield 'time' => [
            'tag' => 'time',
            'expected' => <<<EXPECTED
<div>
<label for="testform-time">Time Field</label>
<input type="time" id="testform-time" name="TestForm[time]" value>
</div>

EXPECTED,
        ];
        yield 'url' => [
            'tag' => 'url',
            'expected' => <<<EXPECTED
<div>
<label for="testform-url">Url Field</label>
<input type="url" id="testform-url" name="TestForm[url]" value>
</div>

EXPECTED,
        ];
    }

    public static function errorSummaryProvider(): Generator
    {
        yield 'errorSummary' => [
            'name' => 'errorSummary',
            'tag' => 'errorSummary',
            'expected' => <<<'EXPECTED'
<div>
<ul>
<li>Email cannot be blank.</li>
<li>Email is not a valid email address.</li>
<li>Text cannot be blank.</li>
<li>Text is invalid.</li>
</ul>
</div>

EXPECTED,
        ];
        yield 'errorSummary Only First' => [
            'name' => 'errorSummaryOnlyFirst',
            'tag' => 'errorSummary',
            'expected' => <<<'EXPECTED'
<div>
<ul>
<li>Email cannot be blank.</li>
<li>Text cannot be blank.</li>
</ul>
</div>

EXPECTED,
        ];
        yield 'errorSummary Header Footer' => [
            'name' => 'errorSummaryHeaderFooter',
            'tag' => 'errorSummary',
            'expected' => sprintf(<<<'EXPECTED'
<div>
<div>%s</div>
<ul>
<li>Email cannot be blank.</li>
<li>Text cannot be blank.</li>
</ul>
<div>%s</div>
</div>

EXPECTED,
                self::ERROR_SUMMARY_HEADER,
                self::ERROR_SUMMARY_FOOTER,
            ),
        ];
    }

    public static function fieldsetProvider(): Generator
    {
        yield 'fieldset' => [
            'tag' => 'fieldset',
            'expected' => sprintf(
                <<<'EXPECTED'
<div>
<fieldset>
<legend>%s</legend>
<div>
<label for="testform-text">Text Field</label>
<input type="text" id="testform-text" name="TestForm[text]" value>
</div>
</fieldset>
</div>

EXPECTED,
                self::LEGEND,
            ),
        ];
    }

    private function createButtonGroupTemplate(string $tag, ?bool $encode): void
    {
        $template = sprintf(
            <<<'TEMPLATE'
            {buttonGroup%s}
                {resetButton |attributes:[class => 'default']}Reset%s{/resetButton}
                {button |attributes:[class => 'more']}Add{/button}
                {submitButton |attributes:[class => 'primary']}Send{/submitButton}
            {/buttonGroup}
            TEMPLATE,
            (is_bool($encode) ? '|encode:' . ($encode ? 'true' : 'false') : ''),
            (is_bool($encode) ? ' & Clear' : ''),
        );

        file_put_contents(
            self::TEMPLATE_DIR . '/' . $tag
                . (is_bool($encode) ? ($encode ? 'true' : 'false') : '') . '.latte',
            $template,
        );
    }

    private function createButtonTemplate(string $tag): void
    {
        if ($tag === 'image') {
            $template = sprintf(
                <<<'TEMPLATE'
                    {%1$s}%1$s@example.com{/%1$s}
                    TEMPLATE,
                $tag,
            );
        } else {
            $template = sprintf(
                <<<'TEMPLATE'
                    {%1$s}%2$s{/%1$s}
                    TEMPLATE,
                $tag,
                StringHelper::uppercaseFirstCharacterInEachWord(self::$inflector->toWords($tag)),
            );
        }

        file_put_contents(self::TEMPLATE_DIR . "/$tag.latte", $template);
    }

    private function createFieldTemplate(string $tag): void
    {
        $template = sprintf(
            <<<'TEMPLATE'
            {varType Yiisoft\FormModel\FormModel $formModel}
            
            {%1$s $formModel, '%1$s'}
            TEMPLATE,
            $tag,
        );

        file_put_contents(self::TEMPLATE_DIR . "/$tag.latte", $template);
    }

    private function createErrorSummaryTemplate(string $name, string $tag): void
    {
        $template = sprintf(<<<'TEMPLATE'
            {varType Yiisoft\FormModel\FormModel $formModel}
            
            {errorSummary $formModel%s}
            TEMPLATE,
            match ($name) {
                'errorSummaryOnlyFirst' => '|onlyFirst',
                'errorSummaryHeaderFooter' => sprintf(
                    '|onlyFirst|header:%s|footer:%s',
                    "'" . self::ERROR_SUMMARY_HEADER . "'",
                    "'" . self::ERROR_SUMMARY_FOOTER . "'",
                ),
                default => ''
            },
        );

        file_put_contents(self::TEMPLATE_DIR . "/$name.latte", $template);
    }

    private function createFieldsetTemplate(): void
    {
        $template = sprintf(
            <<<'TEMPLATE'
            {varType Yiisoft\FormModel\FormModel $formModel}
            
            {fieldset|legend:'%s'}
            {text $formModel, 'text'}
            {/fieldset}
            TEMPLATE,
            self::LEGEND,
        );

        file_put_contents(self::TEMPLATE_DIR . '/fieldset.latte', $template);
    }

    private function createOptionsFieldTemplate(string $tag): void
    {
        $template = sprintf(
            <<<'TEMPLATE'
            {varType Yiisoft\FormModel\FormModel $formModel}
            {var $options = ['one'=>'One','two'=>'Two','three'=>'Three']}
            
            {%1$s $formModel, '%1$s'|%2$s}
            TEMPLATE,
            $tag, ($tag === 'select' ? 'optionsData:$options' : 'items:$options'),
        );

        file_put_contents(self::TEMPLATE_DIR . "/$tag.latte", $template);
    }
}
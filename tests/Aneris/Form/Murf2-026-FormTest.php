<?php
namespace AnerisTest\FormTest;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Stdlib\Entity\EntityInterface;
use Aneris\Stdlib\Entity\EntityAbstract;
use Aneris\Container\ModuleManager;
use Aneris\Validator\Constraints as Assert;
use Aneris\Mvc\PluginManager;
use Aneris\Mvc\Context;
use Aneris\Http\Request;
use Aneris\Http\Response;

use Aneris\Module\Doctrine\AnnotationReaderProxy as DoctrineAnnotationReaderProxy;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Symfony\Component\Validator\ValidatorBuilder as SymfonyValidatorBuilder;
use Symfony\Component\Validator\Constraints as SymfonyAssert;
use Symfony\Component\Form\Forms as SymfonyForms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension as SymfonyValidatorExtension;

// Test Target Classes are under the namespace flowing;
use Aneris\Form\Element as Form;
// Test Target Classes
use Aneris\Form\Element as Element;
use Aneris\Form\View\FormRenderer;
use Aneris\Form\ElementSelection;
use Aneris\Form\ElementCollection;
use Aneris\Form\FormContextBuilder;

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class Entity extends EntityAbstract
{
    /**
     * @Form\Input(type="email",label="Email")
     * @Assert\Email
     */
    public $email;
    /**
     * @Form\Select(type="checkbox",label="Checkbox")
     */
    public $select;
    /**
     * @Form\Select(type="radio",label="Radio",options={red="Red",green="Green",blue="Blue"})
     */
    public $checkbox;
}

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class Entity2
{
    /**
     * @Form\Input(type="hoge",label="Email")
     */
    public $email;
}

/**
 * @Form\Form(attributes={"action"="/app/form","method"="post"})
 */
class Entity3 extends EntityAbstract
{
    /**
     * @Form\Input(type="email",label="Email Address")
     * @Assert\Email
     */
    public $email;
}

class TestTranslator
{
    public function __construct($serviceManager=null)
    {
        $this->serviceManager = $serviceManager;
    }
    public function translate($message, $domain=null, $locale=null)
    {
        if($domain)
            $domain = ':'.$domain;
        else
            $domain = '';
        return '(translate:'.$message.$domain.')';
    }
}

class ProductSymfony extends EntityAbstract
{
    /** @SymfonyAssert\GreaterThanOrEqual(10) **/
    public $id;
    /** @SymfonyAssert\GreaterThanOrEqual(10) **/
    public $id2;
    /** @SymfonyAssert\GreaterThanOrEqual(100) **/
    public $stock;
}


class FormTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        CacheFactory::clearCache();
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/form');
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/twig');
    }
    public static function tearDownAfterClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/form');
    }

    public function testText()
    {
        $element = new Element();
        $element->name = 'id';
        $element->type = 'text';
        $element->value = 'value';
        $element->label = 'LABEL';
        $element->attributes = array(
            'id'    => 'userid',
            'class' => 'field',
            'placeholder' => 'enter user-id',
        );
        $element->errors = array(
            'ERROR1',
        );

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $this->assertEquals('<label accesskey="n" for="userid">(translate:LABEL)</label>'."\n",$renderer->label($element,array('accesskey'=>'n')));
        $this->assertEquals('<input class="input" type="text" value="value" id="userid" placeholder="(translate:enter user-id)" name="id">'."\n",$renderer->widget($element,array('class'=>'input')));
        $this->assertEquals('<small class="error">(translate:ERROR1)</small>'."\n",$renderer->errors($element,array('class'=>'error')));

        $result = <<<EOT
<label for="userid">(translate:LABEL)</label>
<input type="text" value="value" id="userid" class="field" placeholder="(translate:enter user-id)" name="id">
<small>(translate:ERROR1)</small>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $element = new Element();
        $element->name = 'id';
        $element->type = 'text';
        $element->value = '123';

        $result = <<<EOT
<input type="text" value="123" name="id">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $result = <<<EOT
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass')));

        $element->label = 'label';
        $element->errors = array(
            'ERROR1',
        );
        $result = <<<EOT
<label class="labelClass" for="id">(translate:label)</label>
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
<small class="errorClass">(translate:ERROR1)</small>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass')));

        $result = <<<EOT
<div class="fieldDivClass">
<label class="labelClass" for="id">(translate:label)</label>
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
<small class="errorClass">(translate:ERROR1)</small>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass','fieldDivClass'=>'fieldDivClass')));

        $result = <<<EOT
<div class="fieldDivClass">
<div class="labelDivClass">
<label class="labelClass" for="id">(translate:label)</label>
</div>
<div class="widgetDivClass">
<input class="input" id="id" placeholder="(translate:id-id)" type="text" value="123" name="id">
<small class="errorClass">(translate:ERROR1)</small>
</div>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass','errorClass'=>'errorClass','fieldDivClass'=>'fieldDivClass','labelDivClass'=>'labelDivClass','widgetDivClass'=>'widgetDivClass')));
    }

    public function testTextarea()
    {
        $element = new Element();
        $element->name = 'id';
        $element->type = 'textarea';
        $element->value = 'value';
        $element->label = 'User-Id';
        $element->attributes = array(
            'id'    => 'userid',
            'class' => 'field',
            'placeholder' => 'enter user-id',
        );
        //$element->errors = array(
        //    'invalid id',
        //);

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $this->assertEquals('<label accesskey="n" for="userid">(translate:User-Id)</label>'."\n",$renderer->label($element,array('accesskey'=>'n')));

        $result = <<<EOT
<textarea id="userid" class="field" placeholder="(translate:enter user-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($element));

        $result = <<<EOT
<label for="userid">(translate:User-Id)</label>
<textarea id="userid" class="field" placeholder="(translate:enter user-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $element = new Element();
        $element->name = 'id';
        $element->type = 'textarea';
        $element->value = 'value';

        $result = <<<EOT
<textarea name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element));

        $result = <<<EOT
<textarea class="input" id="id" placeholder="(translate:id-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass')));

        $element->label = 'label';
        $result = <<<EOT
<label class="labelClass" for="id">(translate:label)</label>
<textarea class="input" id="id" placeholder="(translate:id-id)" name="id">
value
</textarea>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($element,array('class'=>'input','id'=>'id','placeholder'=>'id-id','labelClass'=>'labelClass')));
    }

    public function testRadio()
    {
        $collection = new ElementSelection();
        $collection->type = 'radio';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $collection->label = 'LABEL';
        //$collection->multiple = true;
        $collection->attributes = array(
            'id'    => 'select1',
            'class' => 'cssClass',
        );

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'radio';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $element->attributes = array(
            'id'    => 'id1',
            'class' => 'cssClass',
        );
        $element->errors = array(
            'invalid id',
        );
        $collection[$element->value] = $element;

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'radio';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $element->attributes = array(
            'id'    => 'id2',
            'class' => 'cssClass',
        );
        $element->errors = array(
            'invalid id',
        );
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label>(translate:LABEL)</label>
<label for="id1">
<input name="foo" type="radio" checked="checked" value="value1" id="id1" class="cssClass">
(translate:LABEL1)
</label>
<label for="id2">
<input name="foo" type="radio" value="value2" id="id2" class="cssClass">
(translate:LABEL2)
</label>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection));
        $this->assertEquals('<input type="radio" value="value1" id="id1" class="cssClass" name="foo">'."\n",$renderer->widget($collection['value1']));
    }

    public function testRadio2()
    {
        $collection = new ElementSelection();
        $collection->type = 'checkbox';
        $collection->name = 'foo';
        $collection->value = array('value1');
        $collection->label = 'LABEL';
        //$collection->multiple = true;
        //$collection->attributes = array(
        //    'id'    => 'select1',
        //    'class' => 'cssClass',
        //);
        //$element->errors = array(
        //    'invalid id',
        //);

        $element = new Element();
        //$element->name = 'foo[]';
        //$element->type = 'radio';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        //$element->attributes = array(
        //    'id'    => 'id1',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $element = new Element();
        //$element->name = 'foo[]';
        //$element->type = 'radio';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        //$element->attributes = array(
        //    'id'    => 'id2',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
(translate:LABEL1)
</label>
</div>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
(translate:LABEL2)
</label>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','id'=>'id','itemDivClass'=>'itemDivClass','labelClass'=>'labelClass')));

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<label class="itemLabelClass">
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
(translate:LABEL1)
</label>
<label class="itemLabelClass">
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
(translate:LABEL2)
</label>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemLabelClass'=>'itemLabelClass','labelClass'=>'labelClass')));

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
<label class="itemLabelClass">
(translate:LABEL1)
</label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
<label class="itemLabelClass">
(translate:LABEL2)
</label>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemLabelClass'=>'itemLabelClass','labelClass'=>'labelClass','outOfLabel'=>true)));

        $result = <<<EOT
<label class="labelClass">(translate:LABEL)</label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1" id="id1">
<label class="itemLabelClass" for="id1">
(translate:LABEL1)
</label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2" id="id2">
<label class="itemLabelClass" for="id2">
(translate:LABEL2)
</label>
EOT;
        $collection['value1']->attributes['id'] = 'id1';
        $collection['value2']->attributes['id'] = 'id2';

        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemLabelClass'=>'itemLabelClass','labelClass'=>'labelClass','outOfLabel'=>true)));
    }

    public function testSelect()
    {
        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = array('value1');
        $collection->label = 'LABEL';
        $collection->multiple = true;
        $collection->attributes = array(
            'id'    => 'select1',
            'class' => 'cssClass',
        );
        //$element->errors = array(
        //    'invalid id',
        //);

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'option';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $element->attributes = array(
            'id'    => 'id1',
            'class' => 'cssClass',
        );
        $collection[$element->value] = $element;

        $element = new Element();
        $element->name = 'foo';
        $element->type = 'option';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $element->attributes = array(
            'id'    => 'id2',
            'class' => 'cssClass',
        );
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label for="select1">(translate:LABEL)</label>
<select name="foo[]" multiple id="select1" class="cssClass">
<option selected="selected" value="value1" id="id1" class="cssClass">
(translate:LABEL1)
</option>
<option value="value2" id="id2" class="cssClass">
(translate:LABEL2)
</option>
</select>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        //echo $renderer->raw($collection);
        $this->assertEquals($result, $renderer->raw($collection));
        //$this->assertEquals('<input id="radio1" class="radioCss" type="radio" value="radio1">'."\n",$renderer->widget($collection['radio1']));
    }

    public function testSelect2()
    {
        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $collection->label = 'LABEL';
        //$collection->multiple = true;
        //$collection->attributes = array(
        //    'id'    => 'select1',
        //    'class' => 'cssClass',
        //);
        //$element->errors = array(
        //    'invalid id',
        //);

        $element = new Element();
        //$element->name = 'foo';
        //$element->type = 'option';
        $element->value = 'value1';
        $element->label = 'LABEL1';
        //$element->attributes = array(
        //    'id'    => 'id1',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $element = new Element();
        //$element->name = 'foo';
        //$element->type = 'option';
        $element->value = 'value2';
        $element->label = 'LABEL2';
        //$element->attributes = array(
        //    'id'    => 'id2',
        //    'class' => 'cssClass',
        //);
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label class="labelClass" for="select1">(translate:LABEL)</label>
<select id="select1" class="cssClass" name="foo">
<option selected="selected" value="value1">
(translate:LABEL1)
</option>
<option value="value2">
(translate:LABEL2)
</option>
</select>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        //echo $renderer->raw($collection,array('id'=>'select1','class'=>'cssClass'));
        $this->assertEquals($result, $renderer->raw($collection,array('id'=>'select1','class'=>'cssClass','labelClass'=>'labelClass')));
        $this->assertEquals('<option id="id1" class="cssClass" value="value1">'."\n",$renderer->openTag('option',$collection['value1'],array('id'=>'id1','class'=>'cssClass', 'value'=>$collection['value1']->value)));
        $this->assertEquals('</option>'."\n",$renderer->closeTag('option',$collection['value1'],array('id'=>'id1','class'=>'cssClass')));
    }

    public function testSwitchRadioAndSelect()
    {
        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $collection->label = 'LABEL';

        $element = new Element();
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $collection[$element->value] = $element;

        $element = new Element();
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $collection[$element->value] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<label for="select1">(translate:LABEL)</label>
<select id="select1" class="cssClass" name="foo">
<option selected="selected" value="value1">
(translate:LABEL1)
</option>
<option value="value2">
(translate:LABEL2)
</option>
</select>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($collection,array('id'=>'select1','class'=>'cssClass')));

        $result = <<<EOT
<label>(translate:LABEL)</label>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo" type="radio" checked="checked" value="value1">
(translate:LABEL1)
</label>
</div>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo" type="radio" value="value2">
(translate:LABEL2)
</label>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);

        $collection->type = 'radio';
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemDivClass'=>'itemDivClass')));

        $result = <<<EOT
<label>(translate:LABEL)</label>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" checked="checked" value="value1">
(translate:LABEL1)
</label>
</div>
<div class="itemDivClass">
<label>
<input class="cssClass" name="foo[]" type="checkbox" value="value2">
(translate:LABEL2)
</label>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);

        $collection->type = 'checkbox';
        $this->assertEquals($result, $renderer->raw($collection,array('class'=>'cssClass','itemDivClass'=>'itemDivClass')));
    }

    public function testForm()
    {
        $form = new ElementCollection();
        $form->type = 'form';
        $form->attributes['action'] = '/foo/bar';
        $form->attributes['method'] = 'POST';

        $element = new Element();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<form class="formClass" action="/foo/bar" method="POST">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->open($form,array('class'=>'formClass')));

        $result = <<<EOT
</form>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->close($form,array('class'=>'formClass')));

        $result = <<<EOT
<form class="formClass" action="/foo/bar" method="POST">
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
</form>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($form,array('class'=>'formClass')));
        $this->assertEquals($result, $renderer->raw($form,array('class'=>'formClass')));
    }

    public function testTheme()
    {
        $form = new ElementCollection();
        $form->type = 'form';

        $element = new Element();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $collection = new ElementSelection();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $form[$collection->name] = $collection;

        $element = new Element();
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $collection[$element->value] = $element;

        $element = new Element();
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $collection[$element->value] = $element;

        // theme class name
        $foundation = 'Aneris\Form\View\Theme\Foundation5Basic';
        $bootstrap = 'Aneris\Form\View\Theme\Bootstrap3Basic';
        $themes = array(
            'default'   => $foundation,
            'bootstrap' => $bootstrap,
        );

        $translator = new TestTranslator();
        $renderer = new FormRenderer($themes,$translator);

        $result = <<<EOT
<div>
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
</div>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="form-group">
<label class="control-label">(translate:LABEL)</label>
<input class="form-control" type="text" value="value" name="boo">
</div>
EOT;
        // theme class
        $renderer->setTheme($bootstrap);
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $form['boo']->errors = array(
            'ERROR1',
        );
        $result = <<<EOT
<div class="form-group has-error">
<label class="control-label">(translate:LABEL)</label>
<input class="form-control" type="text" value="value" name="boo">
<small class="help-block">(translate:ERROR1)</small>
</div>
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="error">
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
<small>(translate:ERROR1)</small>
</div>
EOT;
        // theme alias
        $renderer->setTheme('default');
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="errorfield">
<label>(translate:LABEL)</label>
<input class="form-control" type="text" value="value" name="boo">
<small>(translate:ERROR1)</small>
</div>
EOT;
        // theme immediate
        $themeconfig  = array(
            'field'  => array(
                'success' => array('field'=>array('class'=>true)),
                'error'   => array('field'=>array('class'=>'errorfield')),
            ),
            'widget' => array(
                'default'  => array('class'=>'form-control'),
            ),
        );
        $renderer->setTheme($themeconfig);
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));

        $result = <<<EOT
<div class="row">
<div class="col-2 error">
<label class="right inline">(translate:LABEL)</label>
</div>
<div class="col-10 error">
<input type="text" value="value" name="boo">
<small>(translate:ERROR1)</small>
</div>
</div>
EOT;
        // theme immediate horizontal mode
        $themeconfig  = array(
            'field'  => array(
                'success' => array(
                    'field'=>array('class'=>'row'),
                    'label'=>array('class'=>'col-2'),
                    'widget'=>array('class'=>'col-10'),
                ),
                'error'   => array(
                    'field'=>array('class'=>'row'),
                    'label'=>array('class'=>'col-2 error'),
                    'widget'=>array('class'=>'col-10 error'),
                ),
            ),
            'label' => array(
                'default'  => array('class'=>'right inline'),
            ),
        );
        $renderer->setTheme($themeconfig);
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->raw($form['boo']));
    }

    public function testCloneNestedFormStructure()
    {
        $form = new Element\Form();
        $form->attributes['id'] = 'form1';

        $element = new Element\Input();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $collection = new Element\Select();
        $collection->type = 'select';
        $collection->name = 'foo';
        $collection->value = 'value1';
        $form[$collection->name] = $collection;

        $element = new Element();
        $element->value = 'value1';
        $element->label = 'LABEL1';
        $collection[$element->value] = $element;

        $element = new Element();
        $element->value = 'value2';
        $element->label = 'LABEL2';
        $collection[$element->value] = $element;

        $builder = new FormContextBuilder();
        $form2 = $builder->cloneForm($form);
        $this->assertEquals('form1',$form->attributes['id']);
        $this->assertEquals('form1',$form2->attributes['id']);
        $this->assertEquals('value',$form['boo']->value);
        $this->assertEquals('value',$form2['boo']->value);
        $this->assertEquals('value1',$form['foo']['value1']->value);
        $this->assertEquals('value1',$form2['foo']['value1']->value);

        $form2->attributes['id'] = 'form2';
        $form2['boo']->value = 'value2';
        $form2['foo']['value1']->value = 'value2';
        $this->assertEquals('form1',$form->attributes['id']);
        $this->assertEquals('form2',$form2->attributes['id']);
        $this->assertEquals('value',$form['boo']->value);
        $this->assertEquals('value2',$form2['boo']->value);
        $this->assertEquals('value1',$form['foo']['value1']->value);
        $this->assertEquals('value2',$form2['foo']['value1']->value);
    }

    public function testAnnotationBuilder()
    {
        $entity = new Entity();
        $entity->setEmail('hoge@hoge.com');
        $builder = new FormContextBuilder();
        $formContext = $builder->build($entity);
        $formContext->setData($entity);

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>(translate:Email)</label>
<input type="email" value="hoge@hoge.com" name="email">
<label>(translate:Checkbox)</label>
<label>(translate:Radio)</label>
<label>
<input name="checkbox" type="radio" value="red">
(translate:Red)
</label>
<label>
<input name="checkbox" type="radio" value="green">
(translate:Green)
</label>
<label>
<input name="checkbox" type="radio" value="blue">
(translate:Blue)
</label>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "hoge" is not allowed for the field "type" of annotation @Aneris\Form\Element\Input in AnerisTest\FormTest\Entity2::$email:
     */
    public function testAnnotationBuilderTypeError()
    {
        $entity = new Entity2();
        $builder = new FormContextBuilder();
        $form = $builder->build($entity)->getForm();

        $translator = new TestTranslator();
        $renderer = new FormRenderer(null,$translator);

        //$this->assertEquals($result, $renderer->raw($form));
        echo $renderer->widget($form);
    }

    public function testValidation()
    {
        $entity = new Entity();
        $builder = new FormContextBuilder();
        $formContext = $builder->build($entity);
        $formContext->setData(array('email'=>'abc'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('not a well-formed email address.',$form['email']->errors[0]);
    }

    public function testOnModule()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Form\Module' => true,
                    'Aneris\Validator\Module' => true,
                    'Aneris\Stdlib\I18n\Module' => true,
                ),
            ),
            'form' => array(
                'theme' => 'Aneris\Form\View\Theme\Bootstrap3Basic',
                'translator_text_domain' => 'form',
            ),
            'translator' => array(
                'translation_file_patterns' => array(
                    __NAMESPACE__ => array(
                        'type'        => 'Gettext',
                        'base_dir'    => ANERIS_TEST_RESOURCES.'/php/messages',
                        'pattern'     => '%s/LC_MESSAGES/form.mo',
                        'text_domain' => 'form',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Aneris\Form\FormContextBuilder');
        $renderer = $sm->get('Aneris\Form\View\FormRenderer');
        $translator = $sm->get('Aneris\Stdlib\I18n\Translator');
        $translator->setLocale('en_US');

        $entity = new Entity3();
        $formContext = $builder->build($entity);
        $formContext->setData(array('email'=>'abc'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('not a well-formed email address.',$form['email']->errors[0]);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>Translated: Email Address</label>
<input type="email" value="abc" name="email">
<small>not a well-formed email address.</small>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));

        $translator->setLocale('ja_JP');

        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('正しいメールアドレス形式ではありません。',$form['email']->errors[0]);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>Translated in Japanese: Email Address</label>
<input type="email" value="abc" name="email">
<small>正しいメールアドレス形式ではありません。</small>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));
    }


    public function testOnModuleTranslatorZF2()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Form\Module' => true,
                    'Aneris\Validator\Module' => true,
                ),
            ),
            'form' => array(
                'theme' => 'Aneris\Form\View\Theme\Bootstrap3Basic',
                //'translator_text_domain' => 'form',
            ),
            'translator' => array(
                'translation_file_patterns' => array(
                    __NAMESPACE__ => array(
                        'type'        => 'Gettext',
                        'base_dir'    => ANERIS_TEST_RESOURCES.'/php/messages',
                        'pattern'     => '%s/LC_MESSAGES/form.mo',
                        //'text_domain' => 'form',
                    ),
                ),
            ),
            'container' => array(
                'components' => array(
                    'Aneris\Form\View\FormRenderer' => array(
                        'constructor_args' => array(
                            'translator' => array('ref' => 'Zend\I18n\Translator\Translator'),
                        ),
                    ),
                    'Aneris\Validator\Validator' => array(
                        'constructor_args' => array(
                            'translator' => array('ref' => 'Zend\I18n\Translator\Translator'),
                        ),
                    ),
                    'Zend\I18n\Translator\Translator' => array(
                        'factory' => function($sm) {
                            $config = $sm->get('config');
                            $config = isset($config['translator']) ? $config['translator'] : array();
                            $translator = ZendTranslator::factory($config);
                            return $translator;
                        }
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $builder = $sm->get('Aneris\Form\FormContextBuilder');
        $renderer = $sm->get('Aneris\Form\View\FormRenderer');
        $translator = $sm->get('Zend\I18n\Translator\Translator');
        $translator->setLocale('en_US');

        $entity = new Entity3();
        $formContext = $builder->build($entity);
        $formContext->setData(array('email'=>'abc'));
        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('not a well-formed email address.',$form['email']->errors[0]);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>Translated: Email Address</label>
<input type="email" value="abc" name="email">
<small>not a well-formed email address.</small>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));

        $translator->setLocale('ja_JP');

        $this->assertFalse($formContext->isValid());
        $form = $formContext->getForm();
        $this->assertEquals('正しいメールアドレス形式ではありません。',$form['email']->errors[0]);

        $result = <<<EOT
<form action="/app/form" method="post">
<label>Translated in Japanese: Email Address</label>
<input type="email" value="abc" name="email">
<small>正しいメールアドレス形式ではありません。</small>
</form>
EOT;
        // theme 'default' when implicit
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $this->assertEquals($result, $renderer->widget($formContext->getForm()));
    }

    public function testTwig()
    {
        $form = new ElementCollection();
        $form->type = 'form';

        $element = new Element();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Form\Module' => true,
                    'Aneris\Module\Twig\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'Aneris\Form\View\FormRenderer' => array(
                        'constructor_args' => array(
                            'translator' => array('ref' => 'AnerisTest\FormTest\TestTranslator'),
                        ),
                    ),
                ),
            ),
            'form' => array(
                'theme' => 'Aneris\Form\View\Theme\Bootstrap3Basic',
            ),
            'twig' => array(
                'cache' => CacheFactory::$fileCachePath.'/cache/twig',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $viewManager = $sm->get('Aneris\Module\Twig\TwigView');

        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/form';

        $response = array(
            'form' => $form,
        );
        $templateName = 'index/form';
        $templatePaths = array(ANERIS_TEST_RESOURCES.'/twig/templates');
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            null,
            $sm,
            $pm
        );
        $pm->setConfig(array(),$context);
        $result = <<<EOT
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $content = $viewManager->render($response,$templateName,$templatePaths,$context);
        $this->assertEquals($result,$content);
    }

    public function testBuilderPlugin()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Mvc\Module' => true,
                    'Aneris\Form\Module' => true,
                    'Aneris\Validator\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            null,
            null,
            $sm,
            $pm
        );
        $conf = $sm->get('config');
        $pm->setConfig($conf['mvc']['plugins'],$context);
        $entity = new Entity();
        $entity->setEmail('entity@foo.com');
        global $_POST;
        $_POST['email'] = 'post@foo.com';
        global $_GET;
        $_GET['email'] = 'get@foo.com';
        $formContext = $context->form()->build($entity);
        $this->assertEquals('Aneris\Form\FormContext',get_class($formContext));
        $this->assertEquals('AnerisTest\FormTest\Entity',get_class($formContext->getEntity()));
        $form = $formContext->getForm();
        $this->assertEquals('entity@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'get')->getForm();
        $this->assertEquals('get@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'post')->getForm();
        $this->assertEquals(null,$form['email']->value);

        $form = $context->form()->build($entity,'entity')->getForm();
        $this->assertEquals('entity@foo.com',$form['email']->value);

        $entity2 = new Entity();
        $entity2->setEmail('entity2@foo.com');

        $form = $context->form()->build($entity,null,$entity2)->getForm();
        $this->assertEquals('entity2@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'get',$entity2)->getForm();
        $this->assertEquals('get@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'post',$entity2)->getForm();
        $this->assertEquals(null,$form['email']->value);

        $form = $context->form()->build($entity,'entity',$entity2)->getForm();
        $this->assertEquals('entity2@foo.com',$form['email']->value);

        unset($_SERVER['REQUEST_METHOD']);
        unset($_POST['email']);
        unset($_GET['email']);
    }


    public function testBuilderPlugin2()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Mvc\Module' => true,
                    'Aneris\Form\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            null,
            null,
            $sm,
            $pm
        );
        $conf = $sm->get('config');
        $pm->setConfig($conf['mvc']['plugins'],$context);
        $entity = new Entity();
        $entity->setEmail('entity@foo.com');
        global $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        global $_POST;
        $_POST['email'] = 'post@foo.com';
        global $_GET;
        $_GET['email'] = 'get@foo.com';
        $formContext = $context->form()->build($entity);
        $this->assertEquals('Aneris\Form\FormContext',get_class($formContext));
        $this->assertEquals('AnerisTest\FormTest\Entity',get_class($formContext->getEntity()));
        $form = $formContext->getForm();
        $this->assertEquals('post@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'get')->getForm();
        $this->assertEquals(null,$form['email']->value);

        $form = $context->form()->build($entity,'post')->getForm();
        $this->assertEquals('post@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'entity')->getForm();
        $this->assertEquals('entity@foo.com',$form['email']->value);

        $entity2 = new Entity();
        $entity2->setEmail('entity2@foo.com');

        $form = $context->form()->build($entity,null,$entity2)->getForm();
        $this->assertEquals('post@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'get',$entity2)->getForm();
        $this->assertEquals(null,$form['email']->value);

        $form = $context->form()->build($entity,'post',$entity2)->getForm();
        $this->assertEquals('post@foo.com',$form['email']->value);

        $form = $context->form()->build($entity,'entity',$entity2)->getForm();
        $this->assertEquals('entity2@foo.com',$form['email']->value);

        unset($_SERVER['REQUEST_METHOD']);
        unset($_POST['email']);
        unset($_GET['email']);
    }

    public function testSymfonyValidatorOnSymfonyForm()
    {
        //$validator = Symfony\Component\Validator\Validation::createValidator();

        //$reader = new Doctrine\Common\Annotations\AnnotationReader();
        $reader = new DoctrineAnnotationReaderProxy();
        $validatorBuilder = new SymfonyValidatorBuilder();
        $validatorBuilder->enableAnnotationMapping(
            $reader
        );
        $validator = $validatorBuilder->getValidator();

        $formFactory = SymfonyForms::createFormFactoryBuilder()
            ->addExtension(new SymfonyValidatorExtension($validator))
            ->getFormFactory();

        $product = new ProductSymfony();
        $product->setId(9);
        $product->setId2(10);

        $form = $formFactory->createBuilder('form',$product)
            ->add('id','text')
            ->add('id2','text')
            ->getForm();

        $form->submit(
            array(
                'id'=>9,
                'id2'=>10
            ),
            'POST'
        );

        $this->assertEquals(false,$form->isValid());
        
        $this->assertEquals(1,count($form['id']->getErrors()));
        $this->assertEquals(0,count($form['id2']->getErrors()));
        $errors = $form['id']->getErrors();
        $this->assertEquals("This value should be greater than or equal to '10'.",$errors[0]->getMessage());

    }
}

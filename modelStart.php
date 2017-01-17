#!/usr/local/bin/drush
<?php

// STUFF YOU NEED TO WORRY ABOUT //

// Models, we will make models for everything in here.
$models = [];
$models[] = 'node';
$models[] = 'taxonomy_term';
$models[] = 'paragraph';
$models[] = 'item';
$models[] = 'subcontent';

// We are going to make controllers for these types.
$controllers = [];
$controllers[] = 'node';
$controllers[] = 'taxonomy_term';

// We are going to make teasers for these types.
$teasers = [];
$teasers[] = 'node';
$teasers[] = 'taxonomy_term';

// END STUFF YOU NEED TO WORRY ABOUT //

// Now it's my turn.

// Super star DJs here we go!
// First thing first, get the dev module...
$module = drush_prompt('What is the machine name of the dev module', 'wmcustom');
$umodule = ucwords($module);

// Get the root directory of this drupal.
$root = getRoot();

// Establish the root for the modules.
$modules_root = "/modules/custom/";

// The full module directory.
$module_directory = $root . $modules_root . $module . "/";


// One off files.
// These are files that are not linked to a type/bundle and just need to be made once.
// You can add more here but then make sure to write them lower at the end of the script.
$paragraph_file = $module_directory . "templates/components/paragraph.html.twig";
$wmcontent_file = $module_directory . "templates/components/wmcontent.html.twig";
$imgix_file = $module_directory . "templates/components/imgix-image.html.twig";
$basetrait_file = $module_directory . "src/Entity/Traits/BaseModelTrait.php";

// Get all the bundles for the types we are on.
$modelBundles = getAllBundles($models);

// Get the templates.
$templates = importTemplates();

// Loop through all the bundles.
foreach ($modelBundles as $type => $bundles) {
    // Set an upper case clean type.
    $utype = str_replace(" ", "", ucwords(str_replace("_", " ", $type)));

    // Loop the bundles.
    foreach ($bundles as $bundle) {
        // Set an upper case clean bundle name.
        $ubundle = str_replace(" ", "", ucwords(str_replace("_", " ", $bundle)));

        // Get the model file.
        $model_file = $module_directory . "src/Entity/" . $utype . "/" . $ubundle . ".php";
        // Controller file.
        $controller_file = $module_directory . "src/Controller/" . $utype . "/" . $ubundle . "Controller.php";
        // Twig File.
        $twig_file = $module_directory . "templates/" . $type . "/" . $bundle . "/show.html.twig";
        // Twig Teaser File.
        $twig_teaser_file = $module_directory . "templates/" . $type . "/" . $bundle . "/teaser.html.twig";

        // Hey let's write out the model.
        // First the easy replacements.
        $replacements = [];
        $replacements['utype'] = $utype;
        $replacements['type'] = $type;
        $replacements['ubundle'] = $ubundle;
        $replacements['bundle'] = $bundle;
        $replacements['module'] = $module;

        // Now the fields.
        $fields = getFields($type, $bundle);
        $fields_content = "";
        foreach ($fields as $field) {
            // Loop each field and make a getter function.
            $ufield = str_replace("field_", "", $field);
            $ufield = str_replace(" ", "", ucwords(str_replace("_", " ", $ufield)));

            $field_replacements = [];
            $field_replacements['ubundle'] = $ubundle;
            $field_replacements['ufield'] = $ufield;
            $field_replacements['field'] = $field;

            $fields_content .= replaceSet($field_replacements, $templates['model_field']);

        }
        $replacements['fields'] = $fields_content;

        // Write it out.
        writeFile($model_file, 'model', $replacements);

        // Ok no let's do the twig.
        // First the easy replacements.
        $replacements = [];
        $replacements['utype'] = $utype;
        $replacements['type'] = $type;
        $replacements['ubundle'] = $ubundle;
        $replacements['bundle'] = $bundle;
        $replacements['module'] = $module;
        // Now the fields.
        $fields = getFields($type, $bundle);
        $fields_content = "";
        foreach ($fields as $field) {
            // Loop through each field and call the getter.
            $ufield = str_replace("field_", "", $field);
            $ufield = str_replace(" ", "", ucwords(str_replace("_", " ", $ufield)));

            $field_replacements = [];
            $field_replacements['ufield'] = $ufield;
            $field_replacements['field'] = $field;
            $field_replacements['bundle'] = $bundle;

            $fields_content .= replaceSet($field_replacements, $templates['twig_field']);

        }
        $replacements['fields'] = $fields_content;

        // Write it out.
        // Handle Tax specially.
        if ($type == 'taxnonomy_term') {
            writeFile($twig_file, 'twig_taxonomy', $replacements);
        } else {
            writeFile($twig_file, 'twig', $replacements);
        }


        // Ok we make a controller for this.
        if (in_array($type, $controllers)) {
            // Pretty easy just find replace.
            $replacements = [];
            $replacements['utype'] = $utype;
            $replacements['type'] = $type;
            $replacements['ubundle'] = $ubundle;
            $replacements['bundle'] = $bundle;
            $replacements['module'] = $module;

            writeFile($controller_file, 'controller', $replacements);
        }

        // Ok we make a teaser for this.
        if (in_array($type, $teasers)) {
            $replacements = [];
            $replacements['bundle'] = $bundle;

            // Handle tax specially.
            if ($type == 'taxonomy_term') {
                writeFile($twig_teaser_file, 'twig_teaser_taxonomy', $replacements);
            } else {
                writeFile($twig_teaser_file, 'twig_teaser', $replacements);
            }
        }
    }
}

// Ok now we need to create the one off files.
writeFile($paragraph_file, 'paragraph', []);
writeFile($wmcontent_file, 'wmcontent', []);
writeFile($imgix_file, 'imgix', []);

// The base trait has a replacement.
$replacements = [];
$replacements['module'] = $module;
writeFile($basetrait_file, 'basetrait', $replacements);


// THE END...


/**
 *  __   __  _______  ___      _______  _______  ______    _______
 * |  | |  ||       ||   |    |       ||       ||    _ |  |       |
 * |  |_|  ||    ___||   |    |    _  ||    ___||   | ||  |  _____|
 * |       ||   |___ |   |    |   |_| ||   |___ |   |_||_ | |_____
 * |       ||    ___||   |___ |    ___||    ___||    __  ||_____  |
 * |   _   ||   |___ |       ||   |    |   |___ |   |  | | _____| |
 * |__| |__||_______||_______||___|    |_______||___|  |_||_______|
**/

/**
 * Multiple str_replace().
 *
 * @param $replacements
 * @param $subject
 * @return mixed
 */
function replaceSet($replacements, $subject)
{
    foreach ($replacements as $k => $v) {
        $subject = str_replace("%" . $k . "%", $v, $subject);
    }
    return $subject;
}

/**
 * Get all the fields of this type/bundle.
 *
 * @param $type
 * @param $bundle
 * @return array
 */
function getFields($type, $bundle)
{
    // Do not consider these fields.
    $except = [];
    $except[] = 'title';
    $except[] = 'langcode';
    $except[] = 'tid';
    $except[] = 'id';
    $except[] = 'name';
    $except[] = 'uuid';
    $except[] = 'nid';
    $except[] = 'wmcontent_parent';
    $except[] = 'wmcontent_container';
    $except[] = 'wmcontent_parent_type';
    $except[] = 'wmcontent_weight';
    $except[] = 'menu_link';
    $except[] = 'path';
    $except[] = 'uid';
    $except[] = 'default_langcode';
    $except[] = 'revision_log';
    $except[] = 'revision_uid';
    $except[] = 'revision_timestamp';
    $except[] = 'revision_translation_affected';
    $except[] = 'type';
    $except[] = 'sticky';
    $except[] = 'promote';
    $except[] = 'vid';
    $except[] = 'changed';
    $except[] = 'created';

    // Do not consider these fields specifically for this type.
    if ($type == 'node') {
        // Nothing yet.
    }

    // Do not consider these fields specifically for this type.
    if ($type == 'taxonomy_term') {
        $except[] = 'weight';
    }

    // Get the entity field manager...
    $entityFieldManager = \Drupal::service('entity_field.manager');

    // Get the fields for this type/bundle.
    $fields = $entityFieldManager->getFieldDefinitions($type, $bundle);

    foreach ($except as $e) {
        unset($fields[$e]);
    }

    // We just need the keys.
    $fields = array_keys($fields);

    return $fields;
}

/**
 * Get the root dir of the drupal.
 *
 * @return string
 */
function getRoot()
{
    return \Drupal::root();
}

/**
 * Go through the types and get all the sub bundles.
 *
 * @param $types
 * @return array
 */
function getAllBundles($types)
{
    $service = \Drupal::service('entity_type.bundle.info');
    $bundles = $service->getAllBundleInfo();
    $clean = [];

    foreach ($types as $type) {
        if (isset($bundles[$type])) {
            $clean[$type] = array_keys($bundles[$type]);
        }
    }

    return $clean;
}


/**
 * Write a file using template and the replacements.
 *
 * @param $file
 * @param $template
 * @param $replacements
 */
function writeFile($file, $template, $replacements)
{

    $templates = importTemplates();

    $overwrite_options = array(
        '1' => 'Yes, overwrite this file with new content that I probably will not like.',
        '2' => 'Output the content to the screen so that I can copy and paste.',
    );

    $good_to_write = true;
    if (file_exists($file)) {
        drush_set_error('The file: ' . $file . " exists already!");
        $good_to_write = drush_choice($overwrite_options, 'Do you want to overwrite?');
    }

    $content = replaceSet($replacements, $templates[$template]);
    if ($good_to_write == 1) {
        // Create the path if needed.
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        // Write out the file.
        file_put_contents($file, $content);
    } elseif ($good_to_write == 2) {
        drush_print($content);
    }
}


/**
 *  _______ ______ __  __ _____  _            _______ ______  _____
 * |__   __|  ____|  \/  |  __ \| |        /\|__   __|  ____|/ ____|
 *    | |  | |__  | \  / | |__) | |       /  \  | |  | |__  | (___
 *    | |  |  __| | |\/| |  ___/| |      / /\ \ | |  |  __|  \___ \
 *    | |  | |____| |  | | |    | |____ / ____ \| |  | |____ ____) |
 *    |_|  |______|_|  |_|_|    |______/_/    \_\_|  |______|_____/
 **/

/**
 * In order to fit everything in one easy to move around file
 * we have to do a little bit of dirty work here.
 *
 * These are the templates. Edit then, modify them, create new.
 */
function importTemplates()
{

// Main $templates array.
    $templates = [];

// We use heredocs because it seems like the nicest way to make a big,
// editable string. All though it might make some of the PSR freaks angry.

// %tag% ideally is a replaceable tag.
// with heredocs you need to escape some chars, like:
// $ -> \$


    // The model.
    $templates['model'] = <<<EOT
<?php

namespace Drupal\wmcustom\Entity\%utype%;

use Drupal\wmmodel\Entity\Interfaces\WmModelInterface;
use Drupal\wmmodel\Entity\Traits\WmModel;
use Drupal\%module%\Entity\Traits\BaseModelTrait;

/**
 * Class %ubundle%
 * @package Drupal\wmcustom\Entity\%utype%
 */
class %ubundle% extends %utype% implements WmModelInterface
{
    use WmModel;
    use BaseModelTrait;
    
    %fields%
}
EOT;

    // The field in the model.
    $templates['model_field'] = <<<EOT

    /**
     *   Return the values for %field%.
     */
    public function get%ufield%()
    {
        return "TODO: %ubundle% Model function get%ufield%()";
    }

EOT;

    // Twig file base.
    $templates['twig'] = <<<EOT
{# @var %bundle% \Drupal\%module%\Entity\%utype%\%ubundle% #}
<div>
    {{ %bundle%.getTitle() }}
</div>
%fields%
EOT;

    $templates['twig_taxonomy_term'] = <<<EOT
{# @var %bundle% \Drupal\%module%\Entity\%utype%\%ubundle% #}
<div>
    {{ %bundle%.getName() }}
</div>
%fields%
EOT;

    $templates['twig_teaser'] = <<<EOT
{# @var %bundle% \Drupal\%module%\Entity\%utype%\%ubundle% #}
<div class="teaser">
    <a href="{{ %bundle%.getUrl() }}">
        {{ %bundle%.getTitle() }}
    </a>
</div>
EOT;

    $templates['twig_teaser_taxonomy'] = <<<EOT
{# @var %bundle% \Drupal\%module%\Entity\%utype%\%ubundle% #}
<div class="teaser">
    <a href="{{ %bundle%.getUrl() }}">
        {{ %bundle%.getTitle() }}
    </a>
</div>
EOT;

    $templates['twig_field'] = <<<EOT

<div>
    {{ %bundle%.get%ufield%() }}
</div>

EOT;

    $templates['controller'] = <<<EOT
<?php
namespace Drupal\%module%\Controller\%utype%;

use Drupal\wmcontroller\Controller\ControllerBase;
use Drupal\%module%\Entity\%utype%\%ubundle%;

class %ubundle%Controller extends ControllerBase
{
    protected \$templateDir = '%type%.%bundle%';

    public function show(%ubundle% \$%bundle%)
    {
        return \$this->view('show', compact('%bundle%'));
    }
}
EOT;

    $templates['imgix'] = <<<EOT
<div class="imgix-image preset-{{ preset }}">
    <img src="{{ imgix(image.getFile(), preset) }}" alt="{{ image.getTitle() }}" title="{{ image.getTitle() }}" />
    {% if image.getCaption() is not empty %}
        <span class="caption">{{  image.getCaption() }}</span>
    {% endif %}
</div>
EOT;

    $templates['paragraph'] = <<<EOT
    <div class="paragraph paragraph--{{ pargraph.getWmcontentSize() }} paragraph--{{ paragraph.getWmcontentAlignment() }} ">
        {% include '@wmcustom/paragraph/'~ paragraph.bundle() ~'/show.html.twig' %}  
    </div>
EOT;

    $templates['wmcontent'] = <<<EOT
<div class="wmcontent">
    {% for paragraph in wmcontent %}
      {% include '@wmcustom/components/paragraph.html.twig' with {
      'paragraph': paragraph
      } %}
    {% endfor %}
</div>
EOT;


    $templates['basetrait'] = <<<EOT
<?php
namespace Drupal\%module%\Entity\Traits;

use Drupal\imgix\Plugin\Field\FieldType\ImgixFieldType;
use Drupal\wmcontent\WmContentManager;

/**
 * Class BaseModelTrait
 * @package Drupal\%module%\Entity\Traits
 */
trait BaseModelTrait
{
    /**
     * Load an imgix image to a simple array.
     * @param \$fieldName
     * @return ImgixFieldType
     */
    protected function loadImgixImage(\$fieldName)
    {
        if (\$this->hasField(\$fieldName) && !\$this->get(\$fieldName)->isEmpty()) {
            /** @var ImgixFieldType \$field */
            \$field = \$this->get(\$fieldName)->first();
            return \$field;
        } else {
            return null;
        }
    }

    /**
     * @param \$fieldName
     * @return array|\Drupal\Core\Entity\EntityInterface[]
     */
    protected function loadWmContent(\$fieldName)
    {
        /** @var WmContentManager \$wmcontent */
        \$wmcontent = \Drupal::service('wmcontent.manager');
        \$paragraphs = \$wmcontent->getContent(\$this, \$fieldName);
        return \$paragraphs;
    }

    /**
     * Return the url for this.
     * @return mixed
     */
    public function getUrl()
    {
        return \$this->url();
    }


}
EOT;


    return $templates;
}

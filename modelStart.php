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
$button_file = $module_directory . "templates/components/button.html.twig";
$teasers_file = $module_directory . "templates/components/teasers.html.twig";
$imgix_file = $module_directory . "templates/components/imgix-image.html.twig";
$basetrait_file = $module_directory . "src/Entity/Traits/BaseModelTrait.php";
$abstract_controller_file = $module_directory . "src/Controller/AbstractController.php";

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

// The base trait has a replacement.
$replacements = [];
$replacements['module'] = $module;
// Ok now we need to create the one off files.
writeFile($paragraph_file, 'paragraph', $replacements);
writeFile($abstract_controller_file, 'abstractcontroller', $replacements);
writeFile($wmcontent_file, 'wmcontent', $replacements);
writeFile($basetrait_file, 'basetrait', $replacements);
writeFile($teasers_file, 'teasers', $replacements);

writeFile($button_file, 'button', []);
writeFile($imgix_file, 'imgix', []);


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

namespace Drupal\%module%\Entity\%utype%;

use Drupal\wmmodel\Entity\Interfaces\WmModelInterface;
use Drupal\wmmodel\Entity\Traits\WmModel;
use Drupal\%module%\Entity\Traits\BaseModelTrait;

/**
 * Class %ubundle%
 * @package Drupal\%module%\Entity\%utype%
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

use Drupal\%module%\Controller\AbstractController;
use Drupal\%module%\Entity\%utype%\%ubundle%;

class %ubundle%Controller extends AbstractController
{
    protected \$templateDir = '%type%.%bundle%';

    public function show(%ubundle% \$%bundle%)
    {
        // Add the og Tags
        \$this->applyOgData(\$%bundle%->getOgData());

        return \$this->view('show', compact('%bundle%'))->setEntity(\$%bundle%);
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
    {% set classes = [
        'pargraph',
        'paragraph-size-' ~ pargraph.getWmcontentSize(),
        'paragraph-size-' ~ pargraph.getWmcontentAlignment(),
    ] %}
    <div class="{{ classes|join(' ') }}">
        {% include '@%module%/paragraph/'~ paragraph.bundle() ~'/show.html.twig' %}
    </div>
EOT;

    $templates['wmcontent'] = <<<EOT
<div class="wmcontent">
    {% for paragraph in wmcontent %}
      {% include '@%module%/components/paragraph.html.twig' with {
      'paragraph': paragraph
      } %}
    {% endfor %}
</div>
EOT;

    $templates['button'] = <<<EOT
{% set classes = [
  'btn',
]|merge(class ? class : []) %}

<a {% if external %} target="_blank" {% endif %} href="{{ link }}" class="{{ classes|join(' ') }}">{{ text }}</a>
EOT;

    $templates['teasers'] = <<<EOT
<div>
    {% for item in items %}
        {% set key = item.bundle() %}
        {% set hash = { (key): item } %}
        {% include '@%module%/' ~ item.getEntityTypeId() ~ '/' ~ item.bundle() ~ '/teaser.html.twig' with hash %}
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
        return \$this->url('canonical', ['absolute' => true]);
    }

    /**
     * Give the base OG data
     * @return array
     */
    protected function defaultOgData()
    {
        $og = [];

        $og['og:title'] = [
            'property' => 'og:title',
            'content' => \$this->getLabel(),
        ];
        $og['og:url'] = [
            'property' => 'og:url',
            'content' => \$this->getUrl(),
        ];
        $og['og:description'] = [
            'property' => 'og:description',
            'content' => '',
        ];
        $og['og:image'] = [
            'property' => 'og:image',
            'content' => _%module%_og_image(),
        ];
        $og['og:type'] = [
            'property' => 'og:type',
            'content' => 'article',
        ];

        return $og;
    }

    /**
     * This should be overwritten for each model in the site.
     *
     * However we return basic stuff for now.
     * @return array
     */
    public function getOgData()
    {
        return \$this->defaultOgData();
    }


}
EOT;

    $templates['abstractcontroller'] = <<<EOT
<?php

namespace Drupal\%module%\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Entity;
use Drupal\wmcontroller\Controller\ControllerBase;
use Drupal\%module%\Service\Search\PageMap;
use Drupal\Core\Url;

/**
 * Class AbstractController
 * @package Drupal\%module%\Controller
 */
abstract class AbstractController extends ControllerBase
{
    /**
     * @var ogData
     */
    protected \$ogData;

    /**
     * @var array
     */
    private \$cacheTags = [];

    /**
     * @param string $template
     * @param array $data
     * @return \Drupal\wmcontroller\ViewBuilder\ViewBuilder
     */
    protected function view(\$template = '', \$data = [])
    {
        \$builder =  parent::view(\$template, \$data);

        // Build the OG Tags if they have been set.
        if (is_array(\$this->ogData) && count(\$this->ogData)) {
            // Loop the data.
            foreach (\$this->ogData as \$name => \$data) {
                // Create an element for each data.
                \$el = [
                    '#tag' => 'meta',
                    '#attributes' => [],
                ];

                // Add the property and content hopefully.
                foreach (\$data as \$attribute => \$value) {
                    \$el['#attributes'][\$attribute] = _%module%_truncate(strip_tags(trim(\$value)), 160);
                }

                // Add it to the view builder.
                \$builder->addHeadElement(\$el);
            }
        }

        \$this->addCacheTag('wmsettings');
        if (!empty(\$this->cacheTags)) {
            \$builder->addCacheTags(\$this->cacheTags);
        }

        return \$builder;
    }

    /**
     * @param Entity[] \$entities
     */
    protected function addCacheTagsFromEntities(array \$entities)
    {
        foreach (\$entities as \$entity) {
            \$this->cacheTags = Cache::mergeTags(
                \$this->cacheTags,
                \$entity->getCacheTags()
            );
        }
    }

    /**
     * @param string[]|array \$tags
     */
    protected function addCacheTags(array \$tags)
    {
        \$this->cacheTags = Cache::mergeTags(\$this->cacheTags, \$tags);
    }

    /**
     * @param string \$tag
     */
    protected function addCacheTag(string $tag)
    {
        \$this->cacheTags = Cache::mergeTags(\$this->cacheTags, [\$tag]);
    }

    /**
     * Give the base OG data and set the property.
     * @return array
     */
    protected function defaultOgData()
    {
        \$wmsettings = \Drupal::service('wmsettings.settings');
        \$global = \$wmsettings->read('global');
        \$fb_app_id = \$global->get('fb_app_id')->value;
        \$local = "nl_BE";
        \$image = _%module%_og_image();
        \$title = \$global->get('og_title')->value;
        \$description = \$global->get('og_description')->value;

        \$og = [];

        \$og['og:title'] = [
            'property' => 'og:title',
            'content' => \$title,
        ];
        \$og['og:url'] = [
            'property' => 'og:url',
            'content' => Url::fromRoute('<front>', [], ['absolute' => true])->toString(),
        ];
        \$og['og:description'] = [
            'property' => 'og:description',
            'content' => \$description,
        ];
        \$og['og:image'] = [
            'property' => 'og:image',
            'content' => \$image,
        ];
        \$og['og:image:width'] = [
            'property' => 'og:image:width',
            'content' => '1200',
        ];
        \$og['og:image:height'] = [
            'property' => 'og:image:height',
            'content' => '630',
        ];
        \$og['og:site_name'] = [
            'property' => 'og:site_name',
            'content' => \Drupal::config('system.site')->get('name'),
        ];
        \$og['fb:app_id'] = [
            'property' => 'fb:app_id',
            'content' => \$fb_app_id,
        ];
        \$og['og:locale'] = [
            'property' => 'og:locale',
            'content' => \$local,
        ];
        \$og['og:type'] = [
            'property' => 'og:type',
            'content' => 'website',
        ];

        // Put the default into the property.
        \$this->ogData = \$og;

        // Return the property.
        return \$this->ogData;
    }


    /**
     * Apply a set of OG data ONTOP of the default OD data.
     * @param \$data
     */
    public function applyOgData(array \$data = [])
    {
        \$this->defaultOgData();
        foreach (\$this->ogData as \$k => \$v) {
            if (isset(\$data[\$k])) {
                \$this->ogData[\$k] = \$data[$k];
            }
        }
    }
}

EOT;


    return $templates;
}

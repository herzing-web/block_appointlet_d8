<?php
  /**
   * Created by PhpStorm.
   * User: mwessel
   * Date: 12/16/2019
   * Time: 3:53 PM
   */

  namespace Drupal\block_appointlet\Plugin\Block;

  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

  use Drupal\webform_formsapi\WebformFormsAPI;

  use Drupal\Core\Entity\EntityTypeManager;
  use Drupal\node\Entity\Node;
  use Drupal\node\NodeInterface;

  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * Provides a 'Block Appointlet' block.
   *
   * @Block(
   *  id = "block_appointlet",
   *  admin_label = @Translation("Appointlet Button"),
   * )
   */
  class BlockAppointlet extends BlockBase implements ContainerFactoryPluginInterface {

    protected $node_storage;

    protected $webform_storage;

    protected $forms_api;

    /**
     * Constructs a new BlockAppointlet object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param string $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
     * @param \Drupal\webform_formsapi\WebformFormsAPI $forms_api
     */
    public function __construct( array $configuration, $plugin_id, $plugin_definition, $entityTypeManager, $forms_api ) {
      parent::__construct($configuration, $plugin_id, $plugin_definition);

      $this->node_storage     = $entityTypeManager->getStorage('node');
      $this->webform_storage  = $entityTypeManager->getStorage('webform_submission');

      $this->forms_api        = $forms_api;

    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('entity_type.manager'),
        $container->get('webform_formsapi')
      );

    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
      return [
        'button_text'       => 'Schedule an Appointment',
        'button_class'      => '',
        'campus'            => '',
        'program'           => '',
        'vendor'            => 'INHERZING',
        'vendoraffiliateid' => 'Appointlet',
        'service'           => '60134',
        'utm_source'        => 'leadform',
        'utm_medium'        => '',
        'utm_campaign'      => '',
        'cta_page'          => '',
        'utm_content'       => '',
        'utm_term'          => '',
        'button_template'   => 'block-appointlet-button',
      ] + parent::defaultConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {

      $template_options = $this->get_template_options();
      $campus_codes   = $this->get_campus_options();
      $program_codes  = $this->get_program_options();

//      $form[ 'vertical_tabs' ] = array(
//          '#type' => 'vertical_tabs',
//      );

      $form[ 'button_settings' ] = array(
        '#type'             => 'details',
        '#title'            => 'Button settings',
        '#description'      => "<p>Basic settings for Appointlet.</p>",
//        '#group'            => 'vertical_tabs',
        '#open'             => true,
      );

        $form[ 'button_settings' ][ 'button_template' ] = array(
          '#type'           => 'select',
          '#title'          => t( 'Button display' ),
  //      '#description'    => t( 'Long description of the field use' ),
          '#options'        => $template_options,
          '#default_value'  => $this->configuration['button_template'],
          '#empty_option'   => '--- Select ---',
          '#empty_value'    => '',
          '#required'       => TRUE,
          '#weight'         => '1',
        );


      $form[ 'button_settings' ][ 'button_text' ] = array(
          '#type'           => 'textfield',
          '#title'          => t( 'Button Text' ),
          '#description'    => t( 'Text that will be displayed on the CTA button.' ),
          '#default_value'  => $this->configuration['button_text'] ?? '',
          '#size'           => 200,
          '#maxlength'      => 200,
          '#required'       => TRUE,
        );

        $form[ 'button_settings' ][ 'button_class' ] = array(
          '#type'             => 'textfield',
          '#title'            => t( 'Button Class' ),
          '#description'      => t( 'Add classes that should be added to the chat button element.' ),
          '#default_value'    => $this->configuration['button_class'] ?? '',
          '#size'             => 200,
          '#maxlength'        => 200,
          '#required'         => FALSE,
        );

      $form[ 'form_settings' ] = array(
          '#type'             => 'details',
          '#title'            => 'Form settings',
          '#description'      => "<p>Values that will be sent to the form.</p>",
//        '#group'            => 'vertical_tabs',
          '#open'             => true,
      );

        $form[ 'form_settings' ][ 'campus' ] = array(
            '#type'           => 'select',
            '#title'          => t( 'Campus' ),
            '#description'    => t( 'Select the campus; this will also control the member that will service the appointment.' ),
            '#options'        => $campus_codes,
            '#default_value'  => $this->configuration['campus'] ?? '',
            '#empty_option'   => '--- Select ---',
            '#empty_value'    => '',
            '#required'       => TRUE,
        );

        $form[ 'form_settings' ][ 'program' ] = array(
            '#type'           => 'select',
            '#title'          => t( 'Program' ),
            '#description'    => t( 'Select the program code.' ),
            '#options'        => $program_codes,
            '#default_value'  => $this->configuration['program'] ?? '',
            '#empty_option'   => '--- Select ---',
            '#empty_value'    => '',
            '#required'       => TRUE,
        );

        $form[ 'form_settings' ][ 'vendor' ] = array(
            '#type'           => 'textfield',
            '#title'          => t( 'Vendor code' ),
            '#description'    => t( 'Enter the vendor code that will be sent to Velocify.' ),
            '#default_value'  => $this->configuration['vendor'] ?? '',
            '#size'           => 20,
            '#maxlength'      => 20,
            '#required'       => TRUE,
        );

        $form[ 'form_settings' ][ 'vendoraffiliateid' ] = array(
            '#type'           => 'textfield',
            '#title'          => t( 'Vendor affiliate id code' ),
            '#description'    => t( 'Enter the vendoraffiliateid code that will be sent to Velocify.' ),
            '#default_value'  => $this->configuration['vendoraffiliateid'] ?? '',
            '#size'           => 20,
            '#maxlength'      => 20,
            '#required'       => TRUE,
        );

      $form[ 'appointlet_settings' ] = array(
        '#type'             => 'details',
        '#title'            => 'Form settings',
        '#description'      => "<p>Values that will be sent to the Appointlet form.</p>",
//        '#group'            => 'vertical_tabs',
          '#open'             => true,
      );

      $form[ 'appointlet_settings' ][ 'service' ] = array(
            '#type'           => 'select',
            '#title'          => t( 'Appointment Type' ),
            '#description'    => t( 'Select the appointment type.' ),
            '#options'        => [
                '60134'   => 'Speak with an Adviser',
                '152238'  => 'Schedule a Nursing Tour'
            ],
            '#default_value'  => $this->configuration['service'] ?? '60134',
            '#empty_option'   => '--- Select ---',
            '#empty_value'    => '',
            '#required'       => TRUE,
        );

//        $form[ 'appointlet_settings' ][ 'service' ] = array(
//          '#type'           => 'textfield',
//          '#title'          => t( 'Service code' ),
//          '#description'    => t( 'Enter the Appointlet service ID.  This can be found by going into Appointlet and editing the desired service and copying the integer at the end of the URL.<br><em>Example: Given the URL https://admin.appointlet.com/organizations/17714/services/60134, the Service ID is 60134.</em>' ),
//          '#default_value'  => $this->configuration['service'] ?? '',
//          '#size'           => 20,
//          '#maxlength'      => 20,
//          '#required'       => TRUE,
//        );

      $form['appointlet_settings']['utm_source'] = array(
            '#type'           => 'select',
            '#title'          => 'Source',
            '#description'    => t( 'Select the appointment type.' ),
            '#options'        => [
                'leadform'  => 'Lead form - Create a new lead',
                'thankyou'  => 'Thank you - Update lead with appointment'
            ],
            '#default_value'  => $this->configuration['utm_source'] ?? 'leadform',
            '#empty_option'   => '--- Select ---',
            '#empty_value'    => '',
            '#required'       => TRUE,
        );

//      $form['appointlet_settings']['utm_source'] = array(
//            '#type'           => 'textfield',
//            '#title'          => t('Source'),
//            '#description'    => t('The string that will be sent as the utm_source data and will indicate what type of Appointlet form was used.  <strong>Should be set to <em>leadform</em> to pass Appointlet data to Velocify or <em>thankyou</em> to update an existing Velocify record with appointment details.</strong>'),
//            '#default_value'  => $this->configuration['utm_source'] ?? '',
//            '#size'           => 100,
//            '#maxlength'      => 100,
//            '#required'       => TRUE,
//        );

        $form['appointlet_settings']['utm_campaign'] = array(
            '#type'           => 'textfield',
            '#title'          => t('Campaign'),
            '#description'    => t('The string that will be sent as the utm_campaign data.  <strong>This should be set to the URL of the current page. If left blank, the URL of the current page will be used.</strong>'),
            '#default_value'  => $this->configuration['utm_campaign'] ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

        $form[ 'appointlet_settings' ]['cta_page'] = array(
            '#type'           => 'textfield',
            '#title'          => t('Page Title'),
            '#description'    => t('The string that will be sent as the page data for analytics tracking.  <strong>This should be set to the page title of the current page. If left blank, the title of the current page will be used.</strong>'),
            '#default_value'  => $this->configuration['cta_page'] ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

      $form['appointlet_settings']['utm_medium'] = array(
          '#type'           => 'textfield',
          '#title'          => t('Medium'),
          '#description'    => t('The string that will be sent as the utm_medium data.  Leave blank for no value.'),
          '#default_value'  => $this->configuration['utm_medium'] ?? '',
          '#size'           => 100,
          '#maxlength'      => 100,
          '#required'       => FALSE,
      );

      $form['appointlet_settings']['utm_content'] = array(
            '#type'           => 'textfield',
            '#title'          => t('Content'),
            '#description'    => t('The string that will be sent as the utm_content data.  Leave blank for no value.'),
            '#default_value'  => $this->configuration['utm_content'] ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

        $form['appointlet_settings']['utm_term'] = array(
            '#type'           => 'textfield',
            '#title'          => t('Term'),
            '#description'    => t('The string that will be sent as the utm_term data.  Leave blank for no value.'),
            '#default_value'  => $this->configuration['utm_term'] ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

      return $form;

    }

    /**
     * {@inheritdoc}
     */
    public function blockValidate( $form, FormStateInterface $form_state ) {
      parent::blockValidate( $form, $form_state );
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {

      $this->configuration['button_template']   = $form_state->getValue(['button_settings',     'button_template'], 'block-appointlet-button');
      $this->configuration['button_text']       = $form_state->getValue(['button_settings',     'button_text'], 'Schedule an Appointment');
      $this->configuration['button_class']      = $form_state->getValue(['button_settings',     'button_class'], '');
      $this->configuration['campus']            = $form_state->getValue(['form_settings',       'campus'], 'ONL');
      $this->configuration['program']           = $form_state->getValue(['form_settings',       'program'], 'UNDECID');
      $this->configuration['vendor']            = $form_state->getValue(['form_settings',       'vendor'], 'INHERZING');
      $this->configuration['vendoraffiliateid'] = $form_state->getValue(['form_settings',       'vendoraffiliateid'], 'Appointlet');
      $this->configuration['service']           = $form_state->getValue(['appointlet_settings', 'service'], '60134');
      $this->configuration['utm_source']        = $form_state->getValue(['appointlet_settings', 'utm_source'], 'leadform');
      $this->configuration['utm_medium']        = $form_state->getValue(['appointlet_settings', 'utm_medium'], '');
      $this->configuration['utm_campaign']      = $form_state->getValue(['appointlet_settings', 'utm_campaign'], '');
      $this->configuration['cta_page']          = $form_state->getValue(['appointlet_settings', 'cta_page'], '');
      $this->configuration['utm_content']       = $form_state->getValue(['appointlet_settings', 'utm_content'], '');
      $this->configuration['utm_term']          = $form_state->getValue(['appointlet_settings', 'utm_term'], '');

    }

    /**
     * {@inheritdoc}
     */
    public function build() {

      $build  = [];
      $data   = [];

      $campus_options = $this->get_campus_options();
      $organization   = \Drupal::config('block_appointlet.blockappointletadmin')->get('default_organization');
      $template       = $this->configuration['button_template'];

      $node = \Drupal::routeMatch()->getParameter('node');
      if( !empty( $node) ) {
        $utm_campaign = $node->toUrl('canonical', ['absolute'=>true])->toString();
        $cta_page     = $node->label();
      }
      else{
        $utm_campaign = '';
        $cta_page     = '';
      }

      // get any saved form values
//      $data = $this->forms_api->get_saved_form_values();

      // set up data
      $data['button_text']        = $this->configuration['button_text'];
      $data['button_class']       = $this->configuration['button_class'];

      $data['campus']             = isset($data['campus'])  ? $data['campus']   : $campus_options[$this->configuration['campus']];
      $data['program']            = isset($data['program']) ? $data['program']  : $this->configuration['program'];
      $data['vendor']             = $this->configuration['vendor'];
      $data['vendoraffiliateid']  = $this->configuration['vendoraffiliateid'];
      $data['service']            = $this->configuration['service'];

      $data['utm_source']         = $this->configuration['utm_source'];
      $data['utm_medium']         = $this->configuration['utm_medium'];
      $data['utm_campaign']       = $this->configuration['utm_campaign']  ?: $utm_campaign;
      $data['cta_page']           = $this->configuration['cta_page']      ?: $cta_page;
      $data['utm_content']        = $this->configuration['utm_content'];
      $data['utm_term']           = $this->configuration['utm_term'];

      $data['type']               = trim( strip_tags($this->configuration['button_text']));
      $data['organization']       = $organization;
      $data['bookable']           = $this->configuration['campus'];

      // set up block

      // set caching levels
      $build['#cache'] = array(
          'contexts' => $this->getCacheContexts(),
          'max-age' => $this->getCacheMaxAge(),
      );

      $build['#theme']            = $template;
      $build['#button_data']      = $data;
      $build['#attached']['library'][] = 'block_appointlet/block_appointlet.appointlet_js';
      $build['#attached']['drupalSettings']['block_appointlet']['campus_bookable'] = $campus_options;


      // go! go go go!
      return $build;

    }

    /**
     * The cache contexts associated with this object.
     *
     * These identify a specific variation/representation of the object.
     *
     * Cache contexts are tokens: placeholders that are converted to cache keys by
     * the @cache_contexts_manager service. The replacement value depends on the
     * request context (the current URL, language, and so on). They're converted
     * before storing an object in cache.
     *
     * @return string[]
     *   An array of cache context tokens, used to generate a cache ID.
     *
     * @see \Drupal\Core\Cache\Context\CacheContextsManager::convertTokensToKeys()
     */
    public function getCacheContexts() {
      return ['url.path'];
    }

    /**
     * The maximum age for which this object may be cached.
     *
     * @return int
     *   The maximum time in seconds that this object may be cached.
     */
    public function getCacheMaxAge() {
      return 24 * 60 * 60;  // set to 24 hours
    }

    /**
     * Create template options
     *
     * @return array
     */
    private function get_template_options() {

      $output = [];

      $templates =  block_appointlet_theme();

      foreach( $templates as $key => $template ) {

        if( !empty( $template['option_label'] ) ) {
          $output[$key] = $template['option_label'];
        }

      }

      return $output;

    }

    /**
     * Get campus and campus codes for select
     *
     * @return array
     */
    private function get_campus_options() {

      $output = [];

      $campus_members = \Drupal::config('block_appointlet.blockappointletadmin')->get('campus_members');
      $output         = \Drupal::service('herzing_functions')->string_to_array( $campus_members);
      $output         = array_flip( $output );

      return $output;

    }

    /**
     * Get programs / program degrees for select
     *
     * @return array
     */
    private function get_program_options() {

      $output = [];

      // get all programs
      $program_nids = $this->node_storage
          ->getQuery()
          ->condition('type', 'program' )
          ->condition('status', 1 )
          ->sort( 'title', 'ASC' )
          ->execute();

      $programs = $this->node_storage->loadMultiple( $program_nids );

      // process programs
      foreach( $programs as $program_key => $program ) {

        $promote_degrees  = $program->field_promote_degrees->value ?? '0';
        $program_code     = $program->program_code->value ?? $program->interest_code->value ?? '';

        if( empty( $program_code ) ) { continue; }

        if( $promote_degrees ) {

          // get program degrees
          $program_degree_nids = $this->node_storage
              ->getQuery()
              ->condition('type', 'program_degree' )
              ->condition( 'status', 1 )
              ->condition( 'parent_program', $program_key )
              ->sort( 'title', 'ASC' )
              ->execute();

          $program_degrees = $this->node_storage->loadMultiple( $program_degree_nids );

          // add program degrees to program output
          foreach( $program_degrees as $program_degree ) {

            $program_code     = $program_degree->program_code->value ?? $program_degree->interest_code->value ?? '';

            $output[ $program_code ] = $program_degree->label();

          }

        }
        else {
          $output[ $program_code ] = $program->label();
        }

      }

      asort( $output );

      $output += ['UNDECIDE' => 'Undecided'];

      return $output;

    }

  }

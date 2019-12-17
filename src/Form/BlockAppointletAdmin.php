<?php
  /**
   * Created by PhpStorm.
   * User: mwessel
   * Date: 12/17/2019
   * Time: 7:38 AM
   */

  namespace Drupal\block_appointlet\Form;

  use Drupal\Core\Form\ConfigFormBase;
  use Drupal\Core\Form\FormStateInterface;


  class BlockAppointletAdmin extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
      return [
          'block_appointlet.blockappointletadmin',
      ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'block_appointlet_admin';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

      $config = $this->config('block_appointlet.blockappointletadmin');

      $form   = parent::buildForm($form, $form_state);

      $form[ 'vertical_tabs' ] = array(
          '#type' => 'vertical_tabs',
      );

      $form[ 'appointlet_settings' ] = array(
          '#type'             => 'details',
          '#title'            => 'Appointlet settings',
          '#description'      => "<p>Basic settings for Appointlet.</p>",
          '#group'            => 'vertical_tabs',
      );

        $form[ 'appointlet_settings' ][ 'script_source' ] = array(
            '#type'           => 'textfield',
            '#title'          => '<p>Appointlet JavaScript source</p>',
            '#description'    => '<p>The URL for the source.  Refer to the Website tab in the Sharing area and enter the src value for the script.</p>',
            '#default_value'  => $config->get( 'script_source') ?? '',
            '#size'           => 20,
            '#maxlength'      => 200,
            '#required'       => TRUE,
        );

        $form[ 'appointlet_settings' ][ 'default_organization' ] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Appointlet Organization</p>',
            '#description'    =>'<p>The organization code which will be included with all Appointlet links.</p>',
            '#default_value'  => $config->get( 'default_organization') ?? '',
            '#size'           => 20,
            '#maxlength'      => 200,
            '#required'       => TRUE,
        );

        $form[ 'appointlet_settings' ][ 'default_service' ] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Appointlet Service</p>',
            '#description'    =>'<p>The service code that will be included with all Appointlet Links.</p>',
            '#default_value'  => $config->get( 'default_service') ?? '',
            '#size'           => 20,
            '#maxlength'      => 200,
            '#required'       => TRUE,
        );


      $form['appointlet_fields'] = array(
          '#type'             => 'details',
          '#title'            =>'<p>Appointlet Fields</p>',
          '#description'      =>'<p>Add Appointlet UTM field values</p>',
          '#group'            => 'vertical_tabs',
      );

        $form['appointlet_fields']['utm_source'] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Source</p>',
            '#description'    =>'<p>The string that will be sent as the utm_source data.  Leave blank for no value.</p>',
            '#default_value'  => $config->get( 'utm_source') ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

        $form['appointlet_fields']['utm_medium'] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Medium</p>',
            '#description'    =>'<p>The string that will be sent as the utm_medium data.  Leave blank for no value.</p>',
            '#default_value'  => $config->get( 'utm_medium') ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

        $form['appointlet_fields']['utm_campaign'] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Campaign</p>',
            '#description'    =>'<p>The string that will be sent as the utm_campaign data.  Leave blank for no value.</p>',
            '#default_value'  => $config->get( 'utm_campaign') ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

        $form['appointlet_fields']['utm_content'] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Content</p>',
            '#description'    =>'<p>The string that will be sent as the utm_content data.  Leave blank for no value.</p>',
            '#default_value'  => $config->get( 'utm_content') ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

        $form['appointlet_fields']['utm_term'] = array(
            '#type'           => 'textfield',
            '#title'          =>'<p>Term</p>',
            '#description'    =>'<p>The string that will be sent as the utm_term data.  Leave blank for no value.</p>',
            '#default_value'  => $config->get( 'utm_term') ?? '',
            '#size'           => 100,
            '#maxlength'      => 100,
            '#required'       => FALSE,
        );

      $form['appointlet_fields'] = array(
          '#type'             => 'details',
          '#title'            =>'<p>Campus Settings</p>',
          '#description'      =>'<p>Set relationship between Campus and Appointlet Members</p>',
          '#group'            => 'vertical_tabs',
      );

        $form['appointlet_fields']['campus_members'] = array(
          '#type'             => 'textarea',
          '#title'            => '<p>Campus Members</p>',
          '#description'      => '<p>Enter the campus code and Appointlet member code, one per line, using the format <em>campus code|appointlet</em> member code</em>.<br>Example: <em>AKR|22264</em></p>',
          '#default_value'    => $config->get( 'campus_members') ?? '',
          '#required'         => TRUE,
        );

      return parent::buildForm($form, $form_state);

    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

      parent::submitForm($form, $form_state);

      $fields     = $form_state->cleanValues()->getValues();

      foreach( $fields as $key => $value ) {
        $this->config('block_appointlet.blockappointletadmin')
            ->set($key, $value );
      }

      $this->config('block_appointlet.blockappointletadmin')->save();

    }

  }
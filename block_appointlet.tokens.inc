<?php
/**
 * @file
 * Token integration for the Block Appointlet module.
 *
 * User: mwessel
 * Date: 12/18/2019
 * Time: 1:29 PM
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;
use Drupal\Core\Messenger;
use Drupal\webform_formsapi\WebformFormsAPI;

/**
 * Implements hook_token_info().
 */
function block_appointlet_token_info() {

  $info['types']['appointlet'] = [
      'name'        => 'Appointlet',
      'description' => 'Tokens to generate Appointlet button',
  ];

  $info['tokens']['appointlet']['button'] = [
      'name'        => 'Button',
      'description' => 'Display a button linked to Appointlet',
  ];

  $info['tokens']['appointlet']['attributes'] = [
      'name'        => 'Attributes',
      'description' => 'Generate attributes that will generate an Appointlet form',
  ];

  return $info;

}

/**
 * Replace catalog tokens
 *
 * @param string                                 $type
 * @param array                                  $tokens
 * @param array                                  $data
 * @param array                                  $options
 * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
 *
 * @return array
 */
function block_appointlet_tokens( $type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata ) {

  $replace = [];

  // check if we're dealing with a catalog token
  if( $type !== 'appointlet' ) { return []; }

  $config         = \Drupal::config('block_appointlet.blockappointletadmin');

  $campus_data    = $config->get('campus_members');
  $campus_members = \Drupal::service('herzing_functions')->string_to_array($campus_data);
  $organization   = $config->get('default_organization');
  $saved_data     = \Drupal::service('webform_formsapi')->get_saved_form_values();

  $node = \Drupal::routeMatch()->getParameter('node');
  if( !empty( $node) ) {
    $utm_campaign = $node->toUrl('canonical', ['absolute'=>true])->toString();
    $cta_page     = $node->label();
  }
  else{
    $utm_campaign = '';
    $cta_page     = '';
  }

  // run through each token
  foreach( $tokens as $token => $original ) {

    list( $display, $settings ) = explode( ':', $token );

    $token_data = block_appointlet_parse_settings( $settings );

    // check for required fields
    if( empty( $token_data['button_text'] ) )       { \Drupal::messenger()->addError( "Required value for 'button_text' is missing" ); }
    if( empty( $token_data['campus'] ) )            { \Drupal::messenger()->addError( "Required value for 'campus' is missing" ); }
    if( empty( $token_data['program'] ) )           { \Drupal::messenger()->addError( "Required value for 'program' is missing" ); }
    if( empty( $token_data['vendor'] ) )            { \Drupal::messenger()->addError( "Required value for 'vendor' is missing" ); }
    if( empty( $token_data['vendoraffiliateid'] ) ) { \Drupal::messenger()->addError( "Required value for 'vendoraffiliateid' is missing" ); }
    if( empty( $token_data['service'] ) )           { \Drupal::messenger()->addError( "Required value for 'service' is missing" ); }
    if( empty( $token_data['utm_source'] ) )        { \Drupal::messenger()->addError( "Required value for 'utm_source' is missing" ); }
    if( empty( $token_data['utm_medium'] ) )        { \Drupal::messenger()->addError( "Required value for 'utm_medium' is missing" ); }
//    if( empty( $token_data['utm_campaign'] ) )      { \Drupal::messenger()->addError( "Required value for 'utm_campaign' is missing" ); }
//    if( empty( $token_data['cta_page'] ) )          { \Drupal::messenger()->addError( "Required value for 'cta_page' is missing" ); }

    $data = array_merge( $token_data, $saved_data );

    $data['type']         = trim( strip_tags($data['button_text']));
    $data['organization'] = $organization;
    $data['bookable']     = $campus_members[$data['campus'] ?? 'ONL'];
    $data['campus']       = $data['campus']       ?: $campus_members[$data['campus'] ?? $config->get('campus')];
    $data['program']      = $data['program']      ?: $config->get('program');

    $data['utm_campaign'] = $data['utm_campaign'] ?: $utm_campaign;
    $data['cta_page']     = $data['cta_page']     ?: $cta_page;

    // set up build array
    $build = [];
    $build['#button_data']            = $data;
    $build['#attached']['library'][]  = 'block_appointlet/block_appointlet.appointlet_js';

    // render token
    switch( $display ) {

      case 'attributes':
        $build['#theme'] = 'block_appointlet_attributes';
        break;

      case 'button':
      default:
        $build['#theme'] = 'block_appointlet_button';
        break;

    }

    $output = \Drupal::service('renderer')->render($build);

    $replace[ $original ] = $output;

  }

  // are we done yet?  can I go now?
  return $replace;

}

/**
 * Parse settings from token
 *
 * @param $settings
 *
 * @return array
 */
function block_appointlet_parse_settings( $settings ) {

  // get default url and page name
  $node = \Drupal::routeMatch()->getParameter('node');
  if( !empty( $node ) ) {
    $utm_campaign = $node->toUrl('canonical', ['absolute'=>true])->toString();
    $cta_page     = $node->label();
  }
  else{
    $utm_campaign = '';
    $cta_page     = '';
  }

  // set up default setting values
  $output = [
    'button_text'       => 'Schedule an Appointment',
    'button_class'      => '',
    'campus'            => '',
    'program'           => '',
    'vendor'            => 'INHERZING',
    'vendoraffiliateid' => 'Appointlet',
    'service'           => '60134',
    'utm_source'        => 'leadform',
    'utm_medium'        => '',
    'utm_campaign'      => $utm_campaign,
    'cta_page'          => $cta_page,
    'utm_content'       => '',
    'utm_term'          => '',
  ];


  // parse through settings
  foreach( explode( '|', $settings ) as $item ) {

    list( $key, $value ) = explode( '=', $item );

    $output[$key] = $value ?? '';

  }

  // away wit you
  return $output;

}

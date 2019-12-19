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

  $campus_data    = \Drupal::config('block_appointlet.blockappointletadmin')->get('campus_members');
  $campus_members = \Drupal::service('herzing_functions')->string_to_array($campus_data);
  $organization   = \Drupal::config('block_appointlet.blockappointletadmin')->get('default_organization');

  // run through each token
  foreach( $tokens as $token => $original ) {
    
    list( $display, $settings ) = explode( ':', $token );

    $data = block_appointlet_parse_settings( $settings );

    $data['type']               = trim( strip_tags($data['button_text']));
    $data['organization']       = $organization;
    $data['bookable']           = $campus_members[$data['campus']];

    $build = [];
    $build['#button_data']      = $data;
    $build['#attached']['library'][] = 'block_appointlet/block_appointlet.appointlet_js';

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

function block_appointlet_parse_settings( $settings ) {

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
    'utm_campaign'      => '',
    'cta_page'          => '',
    'utm_content'       => '',
    'utm_term'          => '',
  ];


  foreach( explode( '|', $settings ) as $item ) {

    list( $key, $value ) = explode( '=', $item );

    $output[$key] = $value ?? '';

  }

  return $output;

}

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

  use Symfony\Component\DependencyInjection\ContainerInterface;

  class BlockAppointlet extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * Constructs a new BlockAppointlet object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param string $plugin_definition
     *   The plugin implementation definition.
     */
    public function __construct( array $configuration, $plugin_id, $plugin_definition ) {
      parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition
      );

    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
      return [
        'grid_template' => '',
        'grid_display'  => 'default',
        'campus'        => '',
        'degree'        => '',
      ] + parent::defaultConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {

      $form = [];

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

//      $this->configuration['grid_template'] = $form_state->getValue('grid_template');
//      $this->configuration['grid_display']  = $form_state->getValue('grid_display');
//      $this->configuration['campus']        = $form_state->getValue('campus');
//      $this->configuration['degree']        = $form_state->getValue('degree');

    }

    /**
     * {@inheritdoc}
     */
    public function build() {

      $build = [];

      // set caching levels
//    $build['#cache'] = array(
//        'contexts' => $this->getCacheContexts(),
//        'max-age' => $this->getCacheMaxAge(),
//    );

      $build['#markup'] = '<p>Appointlet Block goes here</p>';

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

  }

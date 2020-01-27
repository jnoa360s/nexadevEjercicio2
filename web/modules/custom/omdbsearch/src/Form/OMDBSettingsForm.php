<?php

/**
 * @file
 * Contains \Drupal\omdbsearch\Form\OMDBSettingsForm
 */

namespace Drupal\omdbsearch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure OMDB API module settings
 */
class OMDBSettingsForm extends ConfigFormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormID()
  {
    return 'omdbsearch_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      'omdbsearch.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL)
  {
    $config = $this->config('omdbsearch.settings');
    $form['omdbapi_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('OMDB API Key'),
      '#default_value' => $config->get('omdbapi_key'),
      '#description' => t('The key to access OMDB API.'),
    );
    $form['array_filter'] = array('#type' => 'value', '#value' => TRUE);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->config('omdbsearch.settings')
      ->set('omdbapi_key', $form_state->getValue('omdbapi_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}




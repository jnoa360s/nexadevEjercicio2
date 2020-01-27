<?php

namespace Drupal\omdbsearch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

/**
 * Class DeleteForm.
 *
 * @package Drupal\mydata\Form
 */
class DeleteMovieForm extends ConfirmFormBase
{

  public $imdbId;
  private $movieTitle;
  private $movieYear;
  private $moviePoster;
  private $movieRating;

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'delete_movie_form';
  }

  public function getQuestion()
  {
    return t('Do you want to delete %movieTitle from your favorites?', array('%movieTitle' => $this->movieTitle));
  }

  public function getCancelUrl()
  {
    return new Url('omdbsearch.report');
  }

  public function getDescription()
  {
    return t('Remove this movie from your favorite movies');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText()
  {
    return t('Remove from favorites');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText()
  {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $imdbId = NULL)
  {

    $client = new GuzzleClient();
    $request = new GuzzleRequest("GET", "http://www.omdbapi.com/?apikey=d1d8751d&i={$imdbId}");
    $response = $client->send($request, ['timeout' => 30]);
    $searchResult = json_decode($response->getBody(), true);

    $this->imdbId = $imdbId;
    $this->moviePoster = $searchResult['Poster'];
    $this->movieTitle = $searchResult['Title'];
    $this->movieYear = $searchResult['Year'];
    $this->movieRating = $searchResult['imdbRating'];

    $headers = array(
      t('Poster'),
      t('Title'),
      t('Year'),
      t('Rating'),
    );
    $form['table_movie'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#empty' => t('No entries available.'),
    ];

    $form['table_movie'][0]['col_poster'] = array(
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => array(
        'width' => 150,
        'src' => $searchResult['Poster']
      ),
    );

    $form['table_movie'][0]['col_title'] = [
      '#plain_text' => $searchResult['Title'],
    ];

    $form['table_movie'][0]['col_year'] = [
      '#plain_text' => $searchResult['Year']
    ];

    $form['table_movie'][0]['rating'] = [
      '#plain_text' => $searchResult['imdbRating']
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    $query = \Drupal::database('omdbsearch');

    $query->delete('omdbsearch')
      ->condition('movie_imdbid', $this->imdbId)
      ->condition('uid', $user->id())
      ->execute();

    $this->messenger()->addStatus('Movie successfully saved');
    $form_state->setRedirect('omdbsearch.report');
  }

}

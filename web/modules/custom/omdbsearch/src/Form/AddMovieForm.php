<?php

namespace Drupal\omdbsearch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

/**
 * Class DeleteForm.
 *
 * @package Drupal\mydata\Form
 */
class AddMovieForm extends ConfirmFormBase
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
    return 'add_movie_form';
  }

  public function getQuestion()
  {
    return t('Do you want to add %movieTitle to your favorites?', array('%movieTitle' => $this->movieTitle));
  }

  public function getCancelUrl()
  {
    return new Url('omdbsearch.omdb_search');
  }

  public function getDescription()
  {
    return t('Add this movie to your favorite movies');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText()
  {
    return t('Add to favorites');
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
    $database = \Drupal::database();
    $query = $database->select('omdbsearch', 'o');
    $query->fields('o', ['id']);
    $query->condition('uid', $user->id());
    $query->condition('movie_imdbid', $this->imdbId);

    $previous = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    if (count($previous) > 0) {
      $this->messenger()->addError('This movie is already in your favorites.');
    } else {
      $field = array(
        'uid' => $user->id(),
        'nid' => $form_state->getValue('nid'),
        'movie_imdbid' => $this->imdbId,
        'movie_image_url' => $this->moviePoster,
        'movie_title' => $this->movieTitle,
        'movie_year' => $this->movieYear,
        'movie_rating' => $this->movieRating,
        'created' => time(),
      );

      $database->insert('omdbsearch')
        ->fields($field)
        ->execute();

      $this->messenger()->addStatus('Movie successfully saved');
      $form_state->setRedirect('omdbsearch.omdb_search');
    }


  }

}

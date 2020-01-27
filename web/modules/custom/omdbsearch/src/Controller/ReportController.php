<?php
/**
 * @file
 * Contains \Drupal\rsvplist\Controller\ReportController.
 */

namespace Drupal\omdbsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Controller for Favorite Movies Report
 */
class ReportController extends ControllerBase
{

  /**
   * Gets all favorite user movies.
   *
   * @return array
   */
  protected function load()
  {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    $database = \Drupal::database();
    $query = $database->select('omdbsearch', 'o');

    $query->condition('o.uid', $user->id());
    $query->fields('o', ['movie_image_url', 'movie_title', 'movie_year', 'movie_rating', 'movie_imdbid']);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Creates the report page.
   *
   * @return array
   *  Render array for report output.
   */
  public function report()
  {
    $content = array();
    $content['message'] = array(
      '#markup' => $this->t('Here is the list of all your favorite movies.'),
    );
    $headers = array(
      t('Poster'),
      t('Name'),
      t('Year'),
      t('Rating'),
      t('Remove'),
    );
    $content['table'] = array(
      '#type' => 'table',
      '#header' => $headers,
      '#empty' => t('No entries available.'),
    );

    foreach ($entries = $this->load() as $key => $entry) {

      $content['table'][$key]['col_poster'] = array(
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => array(
          'width' => 150,
          'src' => $entry['movie_image_url']
        ),
      );

      $content['table'][$key]['col_title'] = [
        '#plain_text' => $entry['movie_title'],
      ];

      $content['table'][$key]['col_year'] = [
        '#plain_text' => $entry['movie_year']
      ];

      $content['table'][$key]['rating'] = [
        '#plain_text' => $entry['movie_rating']
      ];
      $content['table'][$key]["delete_movie_{$key}"] = [
        '#type' => 'link',
        '#title' => "Delete",
        '#url' => Url::fromRoute('omdbsearch.delete_movie_form', ['imdbId' => $entry['movie_imdbid']]),
      ];

    }
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;
    return $content;
  }

}

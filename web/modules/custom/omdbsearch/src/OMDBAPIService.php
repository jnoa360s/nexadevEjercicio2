<?php

/**
 * @file
 * Contains \Drupal\omdbsearch\OMDBAPIService.
 */

namespace Drupal\omdbsearch;

use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;


/**
 * Class OMDBConnection
 *
 * @package Drupal\omdbapi
 */
class OMDBAPIService
{

  /**
   * @var string API querying method
   */
  protected $method = 'GET';

  /**
   * @var \Drupal\Core\Config\Config OMDB settings
   */
  protected $config = NULL;

  /**
   * @var array Store sensitive API info such as the private_key & password
   */
  protected $sensitiveConfig = [];

  /**
   * OMDBConnection constructor.
   */
  public function __construct()
  {
    $this->config = \Drupal::config('omdbsearch.settings');
  }


  /**
   * Pings the OMDB API for data.
   *
   * @param array $options for Url building
   *
   * @return object
   */
  public function queryEndpoint($options = [])
  {
    try {
      $response = $this->callEndpoint($options);
      return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
      watchdog_exception('omdb', $e);
      return (object)[
        'response_type' => '',
        'response_data' => [],
        'pagination' => (object)[
          'total_count' => 0,
          'current_limit' => 0,
          'current_offset' => 0,
        ],
      ];
    }
  }

  /**
   * Call the OMDB API endpoint.
   *
   * @param array $options
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function callEndpoint($options = [])
  {
    $omdbAPIKey = $this->config->get('omdbapi_key');
    if (!empty($omdbAPIKey)) {
      $url = "http://www.omdbapi.com/?apikey={$omdbAPIKey}";
      if (isset($options['s'])) {
        $url .= "&s={$options['s']}";
      }
      if (isset($options['i'])) {
        $url .= "&i={$options['i']}";
      }
      if (isset($options['t'])) {
        $url .= "&t={$options['t']}";
      }
      $client = new GuzzleClient();
      $request = new GuzzleRequest($this->method, $url);
      return $client->send($request, ['timeout' => 30]);
    }
    return false;
  }


}

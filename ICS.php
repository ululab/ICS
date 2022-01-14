<?php

/**
 * Classe ICS
 * Iubenda Consent Solution
 * Standard per la gestione dei consensi utente
 * Documentazione: @link https://www.iubenda.com/en/help/6484-consent-solution-http-api-documentation
 *
 */
class ICS
{

  /**
  * Codice di risposta http
  *
  * @var int
  */
  public $http_code;

  /**
  * Risposta http
  *
  * @var object
  */
  public $response;

  /**
  * Url chiamata API
  *
  * @var string
  */
  public $url_request;

  /**
  * Metodo utiliazzzto nella chiamata API
  *
  * @var string
  */
  public $method_request;

  /**
  * Metodo utiliazzzto nella chiamata API
  *
  * @var array
  */
  public $body_request;

  /**
  * Costruttore della risposta http
  *
  * @param array $response
  * @param int $http_code
  * @param string $url_request
  */
  public function __construct($response, $http_code, $url_request, $method_request, $body_request = null)
  {
    $this->response = $response;
    $this->http_code = $http_code;
    $this->url_request = $url_request;
    $this->method_request = $method_request;
    $this->body_request = $body_request;
  }

  /*
  |--------------------------------------------------------------------------
  | Chiave Api Iubenda
  |--------------------------------------------------------------------------
  |
  */
  static function key()
  {
    return env('CONSENT_SOLUTION_KEY', 'API_KEY_IS_UNDEFINED');
  }

  /*
  |--------------------------------------------------------------------------
  | Url base api
  |--------------------------------------------------------------------------
  |
  */
  static function base()
  {
    return 'https://consent.iubenda.com/';
  }

  /*
  |--------------------------------------------------------------------------
  | Url prepARE request
  |--------------------------------------------------------------------------
  |
  */
  static function url($path = '')
  {
    return self::base() . $path;
  }

  /*
  |--------------------------------------------------------------------------
  | Preparazione parametri per la chimata GET
  |--------------------------------------------------------------------------
  |
  */
  static function prepareParams($params)
  {
    return http_build_query($params);
  }


  /*
  |--------------------------------------------------------------------------
  | Inizio della richiesta GET
  |--------------------------------------------------------------------------
  |
  */
  static function request($method, $url, $params = null)
  {
    $curl = curl_init();

    if (is_array($url))
    {
      $url = implode('/', $url);
    }

    if ($method == 'GET')
    {
      $url = self::url($url . '?') . self::prepareParams($params);
    }
    else
    {
      $url = self::url($url);
    }

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_RETURNTRANSFER => true,

      CURLOPT_HTTPHEADER => array(
          'ApiKey: ' . self::key(),
          'Content-Type: application/json'
      ),
      CURLOPT_POST => $method == 'POST',
      CURLOPT_POSTFIELDS => json_encode($params),

    ));

    $response = curl_exec($curl);

    $code = curl_getinfo($curl)['http_code'];

    curl_close($curl);

    return new ICS(json_decode($response, true), $code, $url, $method, $params);

  }

  /*
  |--------------------------------------------------------------------------
  | Chiamata POST
  |--------------------------------------------------------------------------
  |
  */
  static function post($url, $params = null)
  {
    return self::request('POST', $url, $params);
  }

  /*
  |--------------------------------------------------------------------------
  | Chiamata GET
  |--------------------------------------------------------------------------
  |
  */
  static function get($url, $params = [])
  {
    return self::request('GET', $url, $params);
  }


  /*
  |--------------------------------------------------------------------------
  | Verifica se la chiamata http ha avuto esito positivo
  |--------------------------------------------------------------------------
  |
  */
  public function successful()
  {
    return $this->http_code >= 200 && $this->http_code < 300;
  }

  /*
  |--------------------------------------------------------------------------
  | Verifica se la chiamata http ha avuto esito negativo
  |--------------------------------------------------------------------------
  |
  */
  public function failed()
  {
    return !$this->successful();
  }

  /*
  |--------------------------------------------------------------------------
  | Ritorna il codice di stato http curl
  |--------------------------------------------------------------------------
  |
  */
  public function httpCode()
  {
    return $this->http_code;
  }

  /*
  |--------------------------------------------------------------------------
  | Consent
  |--------------------------------------------------------------------------
  |
  */
  static function createConsent($arguments)
  {
    return self::post('consent', $arguments);
  }



}

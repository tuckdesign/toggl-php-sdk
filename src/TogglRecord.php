<?php

abstract class TogglRecord {
  static $element_name;
  static $element_plural_name;

  private $data = array();
  private $connection;

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __isset($name) {
    return isset($this->data[$name]);
  }

  public function __unset($name) {
    unset($this->data[$name]);
  }

  protected function setConnection(TogglConnection $connection) {
    $this->connection = $connection;
  }

  public function getConnection() {
    return $this->connection;
  }

  protected function setData(array $data) {
    $this->data = $data;
  }

  public function getData() {
    return $this->data;
  }

  function __construct(TogglConnection $connection, array $data = array()) {
    $this->connection = $connection;
    $this->data = $data;
  }

  public static function load(TogglConnection $connection, $id) {
    if (!is_numeric($id)) {
      throw new TogglException('Invalid load ID ' . $id);
    }

    $class = get_called_class();
    $url = $class::$element_plural_name . '/' . $id;
    $response = $connection->request($connection->getURL($url));
    if (!empty($response->data['data'])) {
      return new $class($connection, $response->data['data']);
    }
    return FALSE;
  }

  public static function loadMultiple(TogglConnection $connection, array $query = array()) {
    $class = get_called_class();
    $response = $connection->request($connection->getUrl($class::$element_plural_name, $query));
    foreach ($response->data['data'] as $key => $record) {
      $response->data['data'][$key] = new $class($connection, $record);
    }
    return $response->data;
  }

  public function save() {
    $options['method'] = !empty($this->id) ? 'PUT' : 'POST';
    $options['data'][$this::$element_name] = $this->data;
    $url = $this::element_plural_name . (!empty($this->id) ? '/' . $this->id : '');
    $response = $this->connection->request($this->getURL($url), $options);
    $this->data = $response->data['data'];
    return TRUE;
  }

  public function delete() {
    if (!empty($this->id)) {
      $options['method'] = 'DELETE';
      $url = $this::$element_plural_name . '/' . $this->id;
      $response = $this->connection->request($this->getURL($url), $options);
    }
    $this->data = array();
    return TRUE;
  }
}
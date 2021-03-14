<?php declare(strict_types=1);

namespace Muvon\KISS;

use Error;

final class Blockchain {
  /**
   * @property array $blockchain_map
   *  Map with key as blockchain code to struct
   *  Struct contains of these keys
   *   class - Name of class that implements BlockchainInterface
   *   args - Closure with returned arguments to lazy load that passed to constructor
   *          Also can be as simple array but in that case it will be executed on init
   *   fraction - fraction for currency
   */
  protected static array $blockchain_map;

  /**
   * Initiazlie with config that we will support
   * Should be called before usage of any methods
   *
   * @param array $blockchain_map
   * @return void
   */
  public static function init(array $blockchain_map): void {
    static::$blockchain_map = $blockchain_map;
  }

  /**
   * Check and create class of blockchain for given currency
   *
   * @param string $currency
   * @return BlockchainInterface
   */
  public static function create(string $currency): BlockchainInterface {
    $config = static::$blockchain_map[$currency] ?? [];
    if (!$config) {
      throw new Error('Cannot find configuration for currency: ' . $currency);
    }

    $args = is_callable($config['args']) ? $config['args']() : $config['args'];
    return new $config['class'](...$args);

  }
}

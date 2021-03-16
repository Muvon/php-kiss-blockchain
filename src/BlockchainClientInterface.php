<?php declare(strict_types=1);

namespace Muvon\KISS;

/**
 * All methods should return struct with error first
 * [error, result]
 * If return value has error, result can be null or any other for debug
 * If return value has no error, result should be final
 * error is string prefixed with e_ and class namespace
 * If we have btc blockchain we return errors like e_btc_...
 *
 * All amounts in responses should be returned as value
 * Value is minor amount that should be int/string
 */
interface BlockchainClientInterface {
  /**
   * Generate new address
   * It should return array with keys
   *   address, public, secret [private, wif, seed, ?]
   *
   * @return array
   */
  public function generateAddress(): array;

  /**
   * Get balance for address
   *
   * @param string $address
   * @return array [err, balance]
   *   Balance as number or false if it was error while api quering
   */
  public function getAddressBalance(string $address): array;

  /**
   * Get transaction for given address
   * in same format @see static::getTx()
   *
   * @param string $address
   * @param int $limit
   * @param int $since_ts
   * @return array [err, txs]
   */
  public function getAddressTxs(string $address, int $limit = 100, int $since_ts = 0): array;

  /**
   * Get only deposit transaction for given address
   * in same format @see static::getTx()
   * *This methods is defined by default in abstract BlockchainClient
   * @see BlockchainClient
   *
   * @param string $address
   * @param int $limit
   * @param int $confirmations
   * @return array [err, txs]
   */
  public function getAddressDepositMap(string $address, int $limit = 100, int $confirmations = 0): array;

  /**
   * Validate address in given network
   *
   * @param string $address
   * @return bool
   */
  public function isAddressValid(string $address): bool;

  /**
   * Get current network fee for sending transaction
   * Return value is just string with value of network currency
   */
  public function getNetworkFee(): array;

  /**
   * Get current block/ledger number
   * Return value is just int number
   *
   * @return array
   */
  public function getBlockNumber(): array;

  /**
   * Get block information by index
   *
   * @param int $block
   * @return array
   *  Struct {block, time, confirmations, total_supply, txs}
   *  Where
   *    hash – hash of the block
   *    block - number of block (same as given in arg)
   *    time - unix timestamp when block confirmed
   *    confirmations - how many blocks past after this one
   *    total_supply – total tokens available in value presentation
   *    txs - list of transaction hashes that included in this block
   */
  public function getBlock(int $block): array;

  /**
   * Get last block data
   * Returns same struct as getBlock method but for last active block
   * *This methods is defined by default in abstract BlockchainClient
   * @see BlockchainClient
   *
   * @return array
   */
  public function getLastBlock(): array;

  /**
   * This method returns total supply of coins in blockchain according to latest block
   * @return string value representation of coins
   */
  public function getTotalSupply(): string;

  /**
   * Get transaction information by hash
   * Returns array with struct
   * Where is
   *  block - which block this transaction belongs to or 0 in case mempool
   *  hash - tx id
   *  value - total amount of transacted value
   *  account - null or address in case we check specific one
   *  balance - null or change of balance in case we check specific address
   *    In case of deposit - balance > 0
   *    In case of transfer out - balance < 0
   *  confirmation - blocks since this tx to current time
   *  from - array of unique source addresses [addr1, addr2, ...]
   *  to - array of destinations with struct {address, value}
   *  fee - total fees of this tx
   *
   * @param string $tx
   * @return array
   */
  public function getTx(string $tx): array;

  /**
   * Send money transfer to blockchain
   *
   * @param array $inputs
   *   One or more inputs for sending transaction in format
   *     [{address, value, secret[]}, ...]
   * @param array $outputs
   *   One or more targets as list of targets
   *     [{address, value}, ...]
   * @param int|string $fee
   *   Fee for total transaction. In value representation (minor amount)
   * @return array
   *  [error, tx]
   * tx represents structure {raw, hash}
   * Used to pass in method submitTx
   * Must have hash index and any count of other fields
   */
  public function signTx(array $inputs, array $outputs, int|string $fee): array;

  /**
   * Submit raw transaction to network
   * To get raw transaction we use sign method
   *
   * @param array $tx returned tx after sign
   * @return array
   */
  public function submitTx(array $tx): array;

  /**
   * Send transaction without middle step of signTx
   * Contains 2 calls signTx and submitTx
   * @see BlockchainClientInterface::signTx
   * @see BlockchainClientInterface::submitTx
   *
   * @param array $inputs
   * @param array $outputs
   * @param int|string $fee
   * @return array
   */
  public function send(array $inputs, array $outputs, int|string $fee = 0): array;

  /**
   * Does blockchain support multiple inputs and outputs in one transaction
   *
   * @return bool
   */
  public function hasMultipleOutputs(): bool;

  /**
   * Method to get confirmations for irreversible transactions
   * If you need to change it just extend class and redefine method
   *
   * @return int
   */
  public function getConfirmations(): int;
}

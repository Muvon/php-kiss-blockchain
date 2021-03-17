<?php
namespace Muvon\KISS;

abstract class BlockchainClient implements BlockchainClientInterface {

  /**
   * Get address deposit map using getAddressTxs method
   * That should be implemented by interface
   *
   * @param string $address
   * @param int $confirmations
   * @param int $since_ts
   * @return array [string|null $err, null|array $txs]
   */
  public function getAddressDepositMap(string $address, int $confirmations = 0, int $since_ts = 0): array {
    [$err, $txs] = $this->getAddressTxs($address, $confirmations, $since_ts);
    if ($err) {
      return [$err, null];
    }

    $tx_map = [];
    foreach ($txs as $tx) {
      if (gmp_cmp($tx['balance'], 0) <= 0) {
        continue;
      }

      $tx_map[$tx['hash']] = $tx;
    }

    return [null, $tx_map];
  }

  /**
   * Get Last block data using getBLockNumber and getBlock method
   * That should be implemented by interface
   * @see BlockchainClientInterface::getBlock()
   *
   * @return array [string|null $err, null|array $block]
   */
  public function getLastBlock(bool $expand = false): array {
    [$err, $index] = $this->getBlockNumber();
    if ($err) {
      return [$err, null];
    }

    return $this->getBlock($index, $expand);
  }

  /**
   * This is combo function that contains 2 calls
   * signTx and sendTx
   * @see BlockchainClientInterface::signTx
   * @see BlockchainClientInterface::submitTx
   *
   * @param array $inputs
   * @param array $outputs
   * @param int|string $fee
   * @return array [string|null $err, string|null $tx_hash]
   */
  public function send(array $inputs, array $outputs, int|string $fee = 0): array {
    [$err, $tx] = $this->signTx($inputs, $outputs, $fee);
    if ($err) {
      return [$err, null];
    }

    return $this->submitTx($tx);
  }
}

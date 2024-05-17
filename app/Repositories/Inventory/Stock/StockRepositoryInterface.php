<?php

namespace App\Repositories\Inventory\Stock;

interface StockRepositoryInterface
{
    // public function create(array $data);

    public function update(string $poId, array $data);

    public function find(string $poId);

    public function findAll();

    public function getWarehouseIdsWithStock($sku);

    public function getAllStocksIdAndSku();

    public function getWarehouseByLoggedInUser();

    public function transferStock($sku, $warehouseIdFrom, $warehouseIdTo, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib, $tanggalDiterima);
    // public function delete(string $poId);
}

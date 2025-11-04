<?php

namespace App\Model;

use App\Config\Database;

class Teknisi
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllTeknisi()
    {
        $query = "SELECT * FROM teknisi ORDER BY nama_teknisi ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTeknisiById($id)
    {
        $query = "SELECT * FROM teknisi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createTeknisi($data)
    {
        $query = "INSERT INTO teknisi (nama_teknisi, alamat, no_hp, photo) VALUES (:nama_teknisi, :alamat, :no_hp, :photo)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nama_teknisi', $data['nama_teknisi']);
        $stmt->bindParam(':alamat', $data['alamat']);
        $stmt->bindParam(':no_hp', $data['no_hp']);
        $stmt->bindParam(':photo', $data['photo']);
        return $stmt->execute();
    }

    public function updateTeknisi($id, $data)
    {
        $query = "UPDATE teknisi SET nama_teknisi = :nama_teknisi, alamat = :alamat, no_hp = :no_hp, photo = :photo WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':nama_teknisi', $data['nama_teknisi']);
        $stmt->bindParam(':alamat', $data['alamat']);
        $stmt->bindParam(':no_hp', $data['no_hp']);
        $stmt->bindParam(':photo', $data['photo']);
        return $stmt->execute();
    }

    public function deleteTeknisi($id)
    {
        $query = "DELETE FROM teknisi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}
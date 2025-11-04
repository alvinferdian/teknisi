<?php
namespace App\Controller;

use App\Model\Teknisi;

class TeknisiController
{
    protected $teknisiModel;

    public function __construct()
    {
        $this->teknisiModel = new Teknisi();
    }

    public function index()
    {
        $teknisiList = $this->teknisiModel->getAllTeknisi();
        require_once __DIR__ . '/../View/templates/teknisi/index.php';
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_teknisi' => $_POST['nama_teknisi'],
                'alamat' => $_POST['alamat'],
                'no_hp' => $_POST['no_hp'],
                'photo' => $_FILES['photo']['name'] ?? null,
            ];
            $this->teknisiModel->addTeknisi($data);
            header('Location: /teknisi');
            exit;
        }
        require_once __DIR__ . '/../View/templates/teknisi/add.php';
    }

    public function edit($id)
    {
        $teknisi = $this->teknisiModel->getTeknisiById($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'nama_teknisi' => $_POST['nama_teknisi'],
                'alamat' => $_POST['alamat'],
                'no_hp' => $_POST['no_hp'],
                'photo' => $_FILES['photo']['name'] ?? null,
            ];
            $this->teknisiModel->updateTeknisi($data);
            header('Location: /teknisi');
            exit;
        }
        require_once __DIR__ . '/../View/templates/teknisi/edit.php';
    }

    public function delete($id)
    {
        $this->teknisiModel->deleteTeknisi($id);
        header('Location: /teknisi');
        exit;
    }
}
?>
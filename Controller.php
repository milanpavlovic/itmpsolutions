<?php

include 'core/init.php';
logged_in_redirect();
include 'includes/overall/header.php';


class Controller extends Init {

    public $search = null;

    public $pageName;

    private function getColumns($table){
        return $this->db->query('DESCRIBE ' . $table)->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ( ! empty($_FILES) && in_array($_FILES[$this->files[0]]['type'], $this->allowedTypes) && ($_FILES[$this->files[0]]['size'] < $this->maxSize)) {
                if ($_FILES['file']['error'] > 0){
                    $this->pageName = 'Error';
                    $this->load(array('message' => 'Return Code: ' . $_FILES[$this->files[0]]['error'], 'error' ));
                } else {
                    if (!is_dir('uploads/' . $this->table))
                        mkdir('uploads/' . $this->table, 0777, TRUE);
                    $filePath = 'uploads/' . $this->table . '/' . uniqid() . '.'
                        . end(explode('/', $_FILES[$this->files[0]]['type']));
                    move_uploaded_file($_FILES[$this->files[0]]['tmp_name'], $filePath);
                    $_POST = array_merge(array($this->files[0] => $filePath), $_POST);
                }
            } elseif(! empty($_FILES) && $_FILES[$this->files[0]]['size'] > 0) {
                $this->pageName = 'Error';
                $this->load(array('message' => 'File not valid, ' . $this->table . ' allowed files: '
                    . implode(', ', $this->allowedTypes) . ', max size (MB): ' . $this->maxSize / 1024 / 1024 ), 'error');
                exit();
            } else {
                $filePath = '';
            }
            $keysF = implode(', ', array_keys($_POST));
            $keysV = ':'.implode(', :', array_keys($_POST));
            $vals  = array_combine(explode(', ', $keysV), $_POST);
            $this->db->prepare("INSERT INTO {$this->table} ($keysF) VALUES ($keysV)")->execute($vals);
            $this->setFlash('Added: ' .  implode(' ', $_POST));
            $this->redirect('read');
        }
        $this->pageName = 'Add new: ' . $this->table;
        $this->load(array('action' => 'create', 'columns' => $this->getColumns($this->table)), 'form');
    }

    public function read(){
        $this->pageName = 'Table: ' . $this->table;
        $this->load(array('data' => $this->db->query('SELECT * FROM ' . $this->table)->fetchAll(PDO::FETCH_ASSOC)));
    }

    public function update($id){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (in_array($_FILES[$this->files[0]]['type'], $this->allowedTypes) && ($_FILES[$this->files[0]]['size'] < $this->maxSize)) {
                if ($_FILES['file']['error'] > 0){
                    $this->pageName = 'Error';
                    $this->load(array('message' => 'Return Code: ' . $_FILES[$this->files[0]]['error'], 'error' ));
                } else {
                    if (!is_dir('uploads/' . $this->table))
                        mkdir('uploads/' . $this->table, 0777, TRUE);
                    $filePath = 'uploads/' . $this->table . '/' . uniqid() . '.'
                        . end(explode('/', $_FILES[$this->files[0]]['type']));
                    move_uploaded_file($_FILES[$this->files[0]]['tmp_name'], $filePath);
                    $_POST = array_merge(array($this->files[0] => $filePath), $_POST);
                }
            } elseif($_FILES[$this->files[0]]['size'] > 0) {
                $this->pageName = 'Error';
                $this->load(array('message' => 'File not valid, ' . $this->table . ' allowed files: '
                    . implode(', ', $this->allowedTypes) . ', max size (MB): ' . $this->maxSize / 1024 / 1024 ), 'error');
                exit();
            }

            $pairs = '';
            foreach($_POST as $key => $val)
                $pairs .= $key . '= :' . $key . ', ';
            $keysV = ':'.implode(', :', array_keys($_POST));
            $vals  = array_combine(explode(', ', $keysV), $_POST);
            $statement = $this->db->prepare('UPDATE ' . $this->table . ' SET ' . trim($pairs, ' ,') . ' WHERE id = :id');
            $statement->bindParam(':id', $id);
            foreach($vals as $key => $val)
                $statement->bindValue($key, $val);
            $statement->execute();
            $this->setFlash('Updated: ' .  implode(' ', $_POST));
            $this->redirect('read');
        }
        $statement = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $statement->execute(array(':id' => $id));
        $this->pageName = "Update {$this->table} data";
        if(! $data = $statement->fetch(PDO::FETCH_ASSOC)){
            $this->load(array('message' => $this->table . ' not found'), 'error');
        } else {
            $this->load(array( 'action' => 'update', 'columns' => $this->getColumns($this->table), 'id' => $id, 'data' => $data), 'form');
        }
    }

    public function delete($id){
        $statement = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $statement->execute(array(':id' => $id));
        if(! $data = $statement->fetch(PDO::FETCH_ASSOC)){
            $this->load(array('message' => $this->table . ' not found'), 'error');
        } else {
            $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id")->execute(array(':id' => $id ));
            $this->setFlash($this->load(array('data' => $data), 'delMsg', true));
            $this->redirect('read');
        }
    }

    public function search(){
        $this->pageName = 'Search results';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->search   = $_POST['search'];
            $results = [];
            $save = $this->table;
            foreach($this->tables as $table){
                $this->table = $table;
                $tableFields = $this->getColumns($table);
                $pairs = '';
                foreach($tableFields as $val)
                    $pairs .= $val . ' LIKE :' . $val . ' OR ';
                $statement = $this->db->prepare('SELECT * FROM ' . $table . ' WHERE ' . trim($pairs, 'OR '));
                foreach($tableFields as $val)
                    $statement->bindValue($val, '%' . $_POST['search'] . '%');
                $statement->execute();
                if(! $data = $statement->fetchAll(PDO::FETCH_ASSOC)){
                    $results[$table] = $this->load(array('message' => 'Nothing found'), 'error', true);
                } else {
                    $results[$table] = $this->load(array('data' => $data), 'read', true);
                }
            }
            $this->table = $save;
            $this->load(array('results' => $results));
        } else {
            $this->load(array('message' => 'Bad request'), 'error');
        }
    }


}

include 'includes/overall/footer.php';
?>
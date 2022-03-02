<?php

class rex_api_massif_usability extends rex_api_function
{
    protected $response  = [];
    protected $published = true;
    protected $success   = true;

    public function execute()
    {

        $method  = rex_request('method', 'string', null);
        $_method = '__' . $method;

        if (!$method || !method_exists($this, $_method)) {
            throw new rex_api_exception("Method '{$method}' doesn't exist");
        }
        try {
            $this->$_method();
        } catch (ErrorException $ex) {
            throw new rex_api_exception($ex->getMessage());
        }
        $this->response['method'] = strtolower($method);
        return new rex_api_result($this->success, $this->response);
    }

    private function __changeStatus()
    {

        $status  = rex_post('status', 'string');
        $data_id = (int)rex_post('data_id', 'int');
        $table   = rex_post('table', 'string');
        $sql     = rex_sql::factory();

        $sql->setTable($table)->setValue('status', $status)->setWhere(['id' => $data_id]);
        try {
            $sql->update();
        } catch (\rex_sql_exception $ex) {
            throw new rex_api_exception($ex->getMessage());
        }
        // flush url path file
        if (rex_addon::get('url')->isAvailable()) {
            rex_file::delete(rex_path::addonCache('url', 'pathlist.php'));
        }

        $this->response['element'] = massif_usability::getStatusColumn($status, $data_id, $table);
    }

    private function __duplicate()
    {

        $id = (int)rex_post('data_id', 'int');
        $table   = rex_post('table', 'string');

        $sql     = rex_sql::factory();

        try {
            $sql->setTable($table);
            $sql->setWhere('id = ' . $id);
            $sql->select('*');
            $sql->getRows();
        } catch (\rex_sql_exception $ex) {
            throw new rex_api_exception($ex->getMessage());
        }

        $iSql = \rex_sql::factory();
        $iSql->setTable($table);
        foreach ($sql->getFieldNames() as $field) {
            if (
                $field == 'title' || $field == 'name' || $field == 'headliner'
            ) {
                $iSql->setValue($field, $sql->getValue($field) . ' – KOPIE');
            } else if ($field == 'status') {
                $iSql->setValue($field, 0);
            } else if ($field != 'id') {
                $iSql->setValue($field, $sql->getValue($field));
            }
        }
        $iSql->insert();
    }

    private function __changeCustom()
    {
        $value  = rex_post('value', 'string');
        $name  = rex_post('name', 'string');
        $data_id = (int)rex_post('data_id', 'int');
        $table   = rex_post('table', 'string');
        $sql     = rex_sql::factory();

        $sql->setTable($table)->setValue($name, $value)->setWhere(['id' => $data_id]);
        try {
            $sql->update();
        } catch (\rex_sql_exception $ex) {
            throw new rex_api_exception($ex->getMessage());
        }

        $this->response['element'] = massif_usability::getCustomColumn($name, $value, $data_id, $table);
    }
}

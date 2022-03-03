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
        $data_id = rex_post('data_id', 'int');
        $table = rex_post('table', 'string');

        $dataset = rex_yform_manager_dataset::get($data_id, $table);
        $dataset->status = $status;

        try {
            $dataset->save();
        } catch (Exception $exception) {
            throw new rex_api_exception($exception->getMessage());
        }

        $this->__regeneratePaths();

        $this->response['element'] = massif_usability::getStatusColumn($status, $data_id, $table);
    }

    private function __duplicate()
    {

        $data_id = rex_post('data_id', 'int');
        $table = rex_post('table', 'string');

        $dataset = rex_yform_manager_dataset::get($data_id, $table);
        $data = $dataset->getData();

        $duplicated_dataset = rex_yform_manager_dataset::create($table);

        foreach ($data as $field => $value) {
            if (
                $field == 'title' || $field == 'name' || $field == 'headliner'
            ) {
                $duplicated_dataset->$field = $value . ' – KOPIE';
            } else if ($field == 'status') {
                $duplicated_dataset->$field = 0;
            } else if ($field != 'id') {
                $duplicated_dataset->$field = $value;
            }
        }

        try {
            $duplicated_dataset->save();
        } catch (Exception $exception) {
            throw new rex_api_exception($exception->getMessage());
        }

        $this->__regeneratePaths();
    }

    private function __changeCustom()
    {
        $value = rex_post('value', 'string');
        $name = rex_post('name', 'string');
        $data_id = (int)rex_post('data_id', 'int');
        $table = rex_post('table', 'string');

        $dataset = rex_yform_manager_dataset::get($data_id, $table);
        $dataset->$name = $value;

        try {
            $dataset->save();
        } catch (Exception $exception) {
            throw new rex_api_exception($exception->getMessage());
        }

        $this->__regeneratePaths();

        $this->response['element'] = massif_usability::getCustomColumn($name, $value, $data_id, $table);
    }

    private function __regeneratePaths()
    {

        // URL addon 
        if (\rex_addon::get('url')->isAvailable()) {

            \rex_file::delete(rex_path::addonCache('url', 'pathlist.php'));

            // URL 2 addon 
            try {
                $profiles = \Url\Profile::getAll();
                if ($profiles) {
                    foreach ($profiles as $profile) {
                        $profile->deleteUrls();
                        $profile->buildUrls();
                    }
                }
            } catch (Exception $exception) {
                throw new rex_api_exception($exception->getMessage());
            }
        }
    }
}

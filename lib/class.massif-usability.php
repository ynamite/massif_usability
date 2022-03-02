<?php

class massif_usability
{

    public static $customToggles = [];

    public static function ep_yform_data_list(\rex_extension_point $ep)
    {

        $list      = $ep->getSubject();
        $table     = $ep->getParam('table');
        $tableName = $table->getTableName();

        $addNameColLink = ['rex_yf_color' => ['name'], 'rex_yf_tag' => ['name'], 'rex_yf_event' => ['headliner'], 'rex_yf_gallery' => ['title'], 'rex_yf_login' => ['username'], 'rex_yf_menu' => ['text_standard'], 'rex_yf_news' => ['headliner'], 'rex_yf_time' => ['title']];
        $showImgInListForTable = ['rex_yf_event' => 'image', 'rex_yf_activity' => 'image', 'rex_yf_gallery' => 'image'];
        $noIdYformTables = ['rex_yf_color', 'rex_yf_gallery', 'rex_yf_tag', 'rex_yf_login', 'rex_yf_menu', 'rex_yf_news', 'rex_yf_time'];
        $checkboxesYformTables = [];

        $_csrf_key = \rex_yform_manager_table::get($tableName)->getCSRFKey();
        $_csrf_params = \rex_csrf_token::factory($_csrf_key)->getUrlParams();

        /*
        *   yform 4 status switch and dupblicate fix, needed until yform_usability is updated
        */

        if (in_array($tableName, $noIdYformTables)) {
            $list->removeColumn("id");
        };

        /*if($tableName == 'rex_yf_projekte') {
            $list = $ep->getSubject();
            $f = 'baujahr';
            $list->setColumnFormat($f, 'custom', function ($params) use($f) {
                return date('Y', strtotime($params['list']->getValue($f)));
            });
        }*/


        foreach ($checkboxesYformTables as $table => $field) {
            if ($tableName == $table) {
                $field = is_array($field) ? $field : [$field];
                foreach ($field as $f) {
                    $list->setColumnFormat($f, 'custom', function ($params) use ($f) {
                        return ($params['list']->getValue($f) == 1) ? '<i class="fa fa-check"></i>' : '';
                    });
                }
            }
        }


        foreach ($showImgInListForTable as $table => $field) {
            if ($tableName == $table) {
                $list->setColumnParams($field, ['func' => 'edit', 'data_id' => '###id###', 'table' => $table, \rex_csrf_token::PARAM => $_csrf_params]);
                $list->setColumnFormat($field, 'custom', function ($params) use ($field) {
                    if ($params['list']->getValue($field))
                        return $params['list']->getColumnLink($field, '<img src="index.php?rex_media_type=rex_media_small&rex_media_file=' . $params['list']->getValue($field) . '" class="thumbnail" style="margin-bottom: 0" />');
                });
            }
        }


        foreach ($addNameColLink as $table => $field) {
            if ($tableName == $table) {

                $field = is_array($field) ? $field : [$field];
                foreach ($field as $f) {
                    $list->setColumnParams($f, ['func' => 'edit', 'data_id' => '###id###', 'table' => $table, \rex_csrf_token::PARAM => $_csrf_params]);
                    $list->setColumnFormat($f, 'custom', function ($params) use ($f) {
                        return $params['list']->getColumnLink($f, $params['list']->getValue($f));
                    });
                }
            }
        }

        //$list = self::addDuplicationTrigger($list, $tableName);
        $list = self::addStatusToggle($list, $tableName);
        $list->setColumnPosition(\rex_i18n::msg('yform_function') . ' ', -1);
        $list = self::addCustomToggles($list, $tableName);



        $ep->setSubject($list);
    }

    public static function ep_yform_action_buttons(\rex_extension_point $ep)
    {
        $actionButtons = $ep->getSubject();

        \rex_extension::register('YFORM_DATA_LIST', function (\rex_extension_point $ep) use ($actionButtons) {
            $list = $ep->getSubject();
            $table     = $ep->getParam('table');
            $tableName = $table->getTableName();
            $list = self::addDuplicationTrigger($actionButtons, $list, $tableName);
            $ep->setSubject($list);
        });
    }

    protected static function addStatusToggle($list, $table)
    {
        $fieldName = 'status';
        $layout = $list->getColumnLayout($fieldName);
        $layout[1] = str_replace(
            '<td ',
            '<td width="96" ',
            $layout[1]
        );
        $list->setColumnLayout($fieldName, $layout);
        $list->setColumnPosition($fieldName, -1);
        $list->setColumnPosition(\rex_i18n::msg('yform_function') . ' ', -1);
        $list->setColumnFormat($fieldName, 'custom', function ($params) use ($table) {
            $status = (int)$params['value'] === 1;
            return self::getStatusColumn($status, $params['list']->getValue('id'), $table);
        });
        return $list;
    }

    public static function getStatusColumn($status, $id, $table)
    {
        $new_status = $status ? '0' : '1';
        $class = $status ? 'rex-online' : 'rex-offline';
        $iconAndText = $status ? '<i class="rex-icon rex-icon-online"></i>&nbsp;online' : '<i class="rex-icon rex-icon-offline"></i>&nbsp;offline';
        return '<a data-status="' . $new_status . '" data-id="' . $id . '" data-table="' . $table . '" class="rex-link-expanded status-toggle ' . $class . '" href="javascript:void(0);" style="white-space:nowrap;">' . $iconAndText . '</a>';
    }

    protected static function addDuplicationTrigger($actionButtons, $list, $table)
    {
        $fieldName = 'duplicate';
        $list->setColumnFormat(rex_i18n::msg('yform_function') . ' ', 'custom', function ($params) use ($fieldName, $table, $actionButtons) {
            $button = ['massif_usability_' . $fieldName => '<a data-id="' . $params['list']->getValue('id') . '" data-table="' . $table . '" class="duplicate-trigger" href="javascript:void(0);" style="white-space:nowrap;"><i class="rex-icon fa fa-copy"></i>&nbsp;kopieren</a>'];
            array_splice($actionButtons, 1, 0, $button); // splice in at position 3
            $fragment = new rex_fragment();
            $fragment->setVar('buttons', $actionButtons, false);
            $buttons = $fragment->parse('yform/manager/action_buttons.php');
            return $buttons;
        });

        return $list;
    }

    public static function registerCustomToggle($tableName, $name, $on, $off)
    {
        self::$customToggles[$tableName][$name]['on'] = $on;
        self::$customToggles[$tableName][$name]['off'] = $off;
    }

    public static function addCustomToggles($list, $table)
    {

        foreach (self::$customToggles as $_table => $toggle) {
            if ($table == $_table) {
                foreach ($toggle as $name => $settings) {
                    $list = massif_usability::addCustomToggle($name, $list, $_table);
                }
            }
        }
        return $list;
    }


    public static function addCustomToggle($name, $list, $table)
    {
        $list->setColumnFormat($name, 'custom', function ($params) use ($name, $table) {
            $value = (int)$params['value'] === 1;
            return self::getCustomColumn($name, $value, $params['list']->getValue('id'), $table);
        });
        return $list;
    }

    public static function getCustomColumn($name, $value, $id, $table)
    {
        $new_value = $value ? '0' : '1';
        $state = $value ? 'on' : 'off';

        $html = $value ? self::$customToggles[$table][$name]['on'] : self::$customToggles[$table][$name]['off'];
        return '<a data-value="' . $new_value . '" data-id="' . $id . '" data-name="' . $name . '" data-table="' . $table . '" class="rex-link-expanded custom-toggle custom-toggle-' . $name . ' custom-toggle-state-' . $state . '" href="javascript:void(0);" style="white-space:nowrap;">' . $html . '</a>';
    }
}

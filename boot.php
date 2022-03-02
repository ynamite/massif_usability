<?php

if (rex::isBackend() && rex::getUser()) {
  rex_view::addJsFile($this->getAssetsUrl('js/scripts.js'));
}

\rex_extension::register('PACKAGES_INCLUDED', function () {
  if (\rex_request('rex-api-call', 'string') == 'massif_usability') {

    $api_result = \rex_api_massif_usability::factory();

    \rex_api_function::handleCall();

    if ($api_result && $api_result->getResult()) {
      \rex_response::cleanOutputBuffers();
      \rex_response::sendContent($api_result->getResult()->toJSON(), 'application/json');
    }
    exit;
  }
}, \rex_extension::EARLY);


if (\rex::isBackend() && \rex::getUser() && \rex_plugin::get('yform', 'manager')) {

  \rex_view::setJsProperty('ajax_url', \rex_url::frontendController(\rex_csrf_token::factory('rex_api_massif_usability')->getUrlParams()));

  \rex_extension::register("YFORM_DATA_LIST", [massif_usability::class, 'ep_yform_data_list']);
  \rex_extension::register("YFORM_DATA_LIST_ACTION_BUTTONS", [massif_usability::class, 'ep_yform_action_buttons']);
}


/*
* Beispiel für ein benutzerdefiniertes Toggle (wie Status offline/online)
*/
// massif_usability::registerCustomToggle('rex_yf_event', 'is_highlight', '<i class="fa fa-star-o"></i>', '<i class="fa fa-star"></i>');

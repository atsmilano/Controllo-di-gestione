<?php
namespace UIComponents;
class Tabs extends UIComponent{
    //l'array tabs contiene le informazioni dei singoli tabs
    //ogni tab è rappresentato da un array associativo con 4 variabili identificate dalle chiavi: tab_id, tab_link, tab_params, tab_name
    //hide_ret_url = true se si intende non includere ret_url nel lijnk del tab
    protected $tabs = array();
    private $tpl;

    public function __construct($tabs_id = "tabs") {
        parent::__construct($tabs_id);
                
        $this->appendHtml(\CoreHelper::getJqueryIncludeHtml());

        $this->tpl = \ffTemplate::factory(COMPONENTS_TPL_DIR);
        $this->tpl->load_file("tabs.html", "main");

        $this->tpl->set_var("tabs_id", $this->id);

        $this->tpl->set_var("active_tab", isset($_REQUEST["gotab"]) ? $_REQUEST["gotab"] : 0);
    }

    //aggiunge un tab al gruppo
    public function addTab (Tab $tab) {        
        $this->tpl->set_var("tab",($tab->getHtml()));
        $this->tpl->parse("SectTab", true);        
    }

    public function getHtml() {        
        $this->appendHtml($this->tpl->rpparse("main",true));          
        return parent::getHtml(false);     
    }
}
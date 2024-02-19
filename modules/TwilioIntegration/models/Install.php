<?php
include_once 'vtlib/Vtiger/Module.php';

class TwilioIntegration_Install {
    public function execute() {
        $this->createModule();
        $this->createFields();
    }

    private function createModule() {
        $module = Vtiger_Module::getInstance('TwilioIntegration');
        if (!$module) {
            $module = new Vtiger_Module();
            $module->name = 'TwilioIntegration';
            $module->parent = 'Tools';
            $module->save();
        }
    }

    private function createFields() {
        $module = Vtiger_Module::getInstance('TwilioIntegration');
        $block = Vtiger_Block::getInstance('LBL_TWILIOINTEGRATION_INFORMATION', $module);

        // Twilio SID
        $field = new Vtiger_Field();
        $field->name = 'twilio_sid';
        $field->label = 'Twilio SID';
        $field->uitype = 2; // Text field
        $field->column = 'twilio_sid';
        $field->columntype = 'VARCHAR(100)';
        $field->typeofdata = 'V~O';
        $field->presence = 0;
        $block->addField($field);

        // Auth Token
        $field = new Vtiger_Field();
        $field->name = 'auth_token';
        $field->label = 'Auth Token';
        $field->uitype = 2; // Text field
        $field->column = 'auth_token';
        $field->columntype = 'VARCHAR(100)';
        $field->typeofdata = 'V~O';
        $field->presence = 0;
        $block->addField($field);

        // Ajoutez d'autres champs si nécessaire

        // Enregistrez les champs
        $field->save($module->id);
    }
}

$installer = new TwilioIntegration_Install();
$installer->execute();

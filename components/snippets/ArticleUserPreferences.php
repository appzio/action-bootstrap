<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleUserpreferences extends ArticleComponent
{
    public function template()
    {
        $this->getAdditionalInformationFields();
    }

    /**
     * Render additional information fields
     */
    protected function getAdditionalInformationFields()
    {
        $fields = $this->getAdditionalInformationFieldNames();

        foreach ($fields as $identifier => $field) {
            $this->renderAdditionalInformationField($identifier, $field);
        }
    }

    protected function getAdditionalInformationFieldNames()
    {
        return array(
            'relationship_status' => 'Status',
            'seeking' => 'They are seeking',
            'religion' => 'Religion',
            'diet' => 'Diet',
            'tobacco' => 'Tobacco',
            'alcohol' => 'Alcohol',
            'zodiac_sign' => 'Zodiac Sign'
        );
    }

    protected function renderAdditionalInformationField(string $identifier, string $field)
    {
        $prefix = isset($this->options['variable_prefix']) ? $this->options['variable_prefix'] : '';

        $onclick = new StdClass();
        $onclick->action = 'open-action';
        $onclick->action_config = $this->factoryobj->getActionidByPermaname('profilestatusselect');
        $onclick->id = $identifier . '|' . $prefix;
        $onclick->open_popup = 1;
        $onclick->sync_open = 1;
        $onclick->back_button = 1;
        $onclick->keep_user_data = 1;

        $content = $this->factoryobj->getVariable($identifier);

        if (!empty($content) && $identifier == 'seeking') {
            // Variable value is json, transform it into text
            $content = json_decode($content);
            $content = join(', ', $content);
            $content = substr($content, 0, 10) . '...';
        }

        $this->factoryobj->data->scroll[] = $this->factoryobj->getHairline('#DADADA');
        $this->factoryobj->data->scroll[] = $this->factoryobj->getRow(array(
            $this->factoryobj->getText($field, array('style' => 'profile_field_label_additional_info')),
            $this->factoryobj->getRow(array(
                $this->factoryobj->getText($content, array(
                    'style' => 'profile_status_value'
                )),
                $this->factoryobj->getImage('arrow.png', array(
                    'width' => '10',
                    'margin' => '17 0 0 0'
                ))
            ), array(
                'floating' => 1,
                'float' => 'right',
                'margin' => '0 20 0 0'
            ))
        ), array(
            'onclick' => $onclick,
            'padding' => '0 0 0 0',
            'background-color' => '#FFFFFF'
        ));
    }
}
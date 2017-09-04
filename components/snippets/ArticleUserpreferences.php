<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleUserpreferences extends ArticleComponent
{
    public $prefix;

    public function template()
    {
        $this->prefix = isset($this->options['variable_prefix']) ? $this->options['variable_prefix'] : '';

        $this->registerDivs();

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
        if ($identifier === 'zodiac_sign') {
            $onclick = new StdClass();
            $onclick->action = 'open-action';
            $onclick->action_config = $this->factoryobj->getActionidByPermaname('profilestatusselect');
            $onclick->id = $identifier . '|' . $this->prefix;
            $onclick->open_popup = 1;
            $onclick->sync_open = 1;
            $onclick->sync_close = 1;
            $onclick->back_button = 1;
            $onclick->keep_user_data = 1;
        } else {
            $onclick = new stdClass();
            $onclick->action = 'show-div';
            $onclick->div_id = $identifier . '_div';
            $onclick->tap_to_close = 1;
            $onclick->transition = 'from-bottom';
            $onclick->background = 'blur';
            $onclick->layout = new stdClass();
            $onclick->layout->top = 50;
            $onclick->layout->right = 10;
            $onclick->layout->left = 10;
        }

        $content = $this->getContent($identifier);

        $this->factoryobj->data->scroll[] = $this->factoryobj->getHairline('#DADADA');
        $this->factoryobj->data->scroll[] = $this->factoryobj->getRow(array(
            $this->factoryobj->getText($field, array('style' => 'profile_field_label_additional_info')),
            $this->factoryobj->getRow(array(
                $this->factoryobj->getText($content, array(
                    'style' => 'profile_status_value'
                )),
                $this->factoryobj->getImage('arrow.png', array(
                    'width' => '10',
                    'vertical-align' => 'middle',
                ))
            ), array(
                'floating' => 1,
                'float' => 'right',
                'vertical-align' => 'middle',
                'margin' => '0 20 0 0',
            ))
        ), array(
            'onclick' => $onclick,
            'background-color' => '#FFFFFF',
        ));
    }

    public function registerDivs()
    {
        $fields = $this->getAdditionalInformationFieldNames();

        foreach ($fields as $key => $title) {
            $options = $this->getStatusData($key);
            $this->registerDiv($key, $title, $options);
        }
    }

    public function registerDiv($identifier, $title, $options)
    {
        $this->factoryobj->copyAssetWithoutProcessing('circle_non_bg.png');
        $this->factoryobj->copyAssetWithoutProcessing('circle_selected_bg.png');

        $selectedState = array('style' => 'radio_selected_state', 'allow_unselect' => 1, 'animation' => 'fade');
        $column = array();

        foreach ($options as $option) {
            $selectedState['variable_value'] = $option;
            $selectedState['active'] = $this->getActiveStatus($this->factoryobj->getVariable($this->prefix . $identifier), $option);

            $variable = $this->getStatusVariable($identifier, $option);

            $column[] = $this->factoryobj->getRow(array(
                $this->factoryobj->getText(ucfirst($option), array(
                    'padding' => '10 10 10 20'
                )),
                $this->factoryobj->getRow(array(
                    $this->factoryobj->getText('', array(
                        'style' => 'radio_default_state',
                        'selected_state' => $selectedState,
                        'variable' => $variable
                    ))
                ), array(
                    'width' => '40%',
                    'floating' => 1,
                    'float' => 'right'
                ))
            ), array(
                'padding' => '5 0 5 0',
                'margin' => '0 0 0 0'
            ));
            $column[] = $this->factoryobj->getHairline('#DADADA');
        }

        $save = new stdClass();
        $save->id = 'save-status-' . $identifier;
        $save->action = 'submit-form-content';

        $close = new stdClass();
        $close->action = 'hide-div';
        $close->div_id = $identifier . '_div';

        $this->factoryobj->data->divs[$identifier . '_div'] = $this->factoryobj->getColumn(array(
            $this->factoryobj->getText(ucfirst($title), array(
                'text-align' => 'center',
                'background-color' => '#FF6600',
                'color' => '#FFFFFF',
                'padding' => '20 0 20 0',
                'margin' => '0 0 0 0',
            )),
            $this->factoryobj->getColumn($column, array(
                'background-color' => '#ffffff'
            )),
            $this->factoryobj->getRow(array(
                $this->factoryobj->getText('Cancel', array(
                    'onclick' => $close,
                    'id' => 'id',
                    'style' => 'desee_general_button_style_footer_half_default'
                )),
                $this->factoryobj->getText('Submit', array(
                    'onclick' => array($save, $close),
                    'id' => 'id',
                    'style' => 'desee_general_button_style_footer_half'
                ))
            ))
        ), array(
            'border-radius' => '3'
        ));
    }

    protected function getStatusVariable($identifier, $field)
    {
        if (!empty($this->prefix)) {
            // Prefixed variables use checkboxes, return unique variable name
            return $this->prefix . $identifier . '_' . $field;
        }

        return $identifier;
    }

    protected function getActiveStatus($status, string $field)
    {
        // If status is not set and we're accessing this NOT from the user profile
        if (!empty($this->prefix) && empty($status)) {
            return '1';
        }

        if (!empty($this->prefix) && !empty($status)) {
            $status = json_decode($status);
            return in_array($field, $status) ? '1' : '0';
        }

        if (empty($this->prefix) && $status) {
            return $status == $field;
        }

        if (is_null($status)) {
            return '0';
        }

        return in_array($field, json_decode($status)) ? '1' : '0';
    }

    protected function getContent($identifier)
    {
        $content = $this->factoryobj->getSavedVariable($this->prefix . $identifier);

        if (!empty($this->prefix)) {
            $content = $this->factoryobj->getVariable($this->prefix . $identifier);

            if (empty($content)) {
                // TODO: temporary like that will be changed
                $content = json_encode($this->getStatusData($identifier));
            }
        }

        if ($this->isJson($content)) {
            // Variable value is json, transform it into text
            $content = json_decode($content);
            $content = join(', ', $content);
            if (strlen($content) > 10) {
                $content = substr($content, 0, 10) . '...';
            }
        }

        $content = empty($content) ? ' ' : $content;

        return $content;
    }

    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Get all fields for each status
     *
     * @return array
     */
    protected function getStatusData($identifier)
    {
        switch ($identifier) {
            case 'relationship_status':
                return array(
                    'Divorced',
                    'Married',
                    'Separated',
                    'Single',
                    'Widowed',
                );
                break;
            case 'seeking':
                return array(
                    'Dating',
                    'Friendship',
                    'Long-Term Relationship',
                    'Marriage'
                );
                break;
            case 'religion':
                return array(
                    'Buddhist',
                    'Christian',
                    'Hindu',
                    'Muslim',
                    'Sikh',
                    'Other'
                );
                break;
            case 'diet':
                return array(
                    'Non-Veg',
                    'Veg'
                );
                break;
            case 'tobacco':
                return array(
                    'Chew',
                    'Smoke',
                    'Vape',
                    'None'
                );
                break;
            case 'alcohol':
                return array(
                    'Yes',
                    'No'
                );
                break;
            case 'zodiac_sign':
                return array(
                    'Aquarius',
                    'Aries',
                    'Cancer',
                    'Capricorn',
                    'Gemini',
                    'Leo',
                    'Libra',
                    'Pisces',
                    'Sagittarius',
                    'Scorpio',
                    'Taurus',
                    'Virgo'
                );
            default:
                return array();
                break;
        }
    }
}

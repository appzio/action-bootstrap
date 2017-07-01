<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkitbox extends ArticleComponent {

    public $user_vars;

    public function template() {

        $title = $this->addParam('title', $this->options, false);
        $variables = $this->addParam('variables', $this->options, false);
        $edit_type = $this->addParam('edit_type', $this->options, false);
        $hide_edit_icon = $this->addParam('hide_edit_icon', $this->options, false);
        $edit_action_id = $this->addParam('edit_action_id', $this->options, false);
        $this->user_vars = $this->addParam('user_vars', $this->options, array());

        $title_color = $this->addParam('title_color', $this->options, '#000000');
        $text_color = $this->addParam('title_color', $this->options, '#4d4e49');
        $background_color = $this->addParam('background_color', $this->options, '#ffffff');

        $sorted_data = $this->getSortedData( $variables );
        $text_data = $sorted_data['data'];
        $var_keys = $sorted_data['variables'];

        $data[] = $this->factoryobj->getRow(array(
            $this->factoryobj->getText( $title, array( 'color' => $title_color, 'font-size' => '21', 'font-weight' => 'bold' ) )
        ), array( 'width' => '100%' ));

        foreach ($text_data as $i => $text) {
            $sub_entry = $this->factoryobj->getText( $text, array( 'color' => $text_color, 'font-size' => '14' ) );

            if ( $edit_type == 'inline' ) {
                $var_id = $this->factoryobj->getVariableId( $var_keys[$i] );
                $text = $this->factoryobj->getVariable( $var_keys[$i] );
                $sub_entry = $this->factoryobj->getFieldtextarea( $text, array( 'variable' => $var_id, 'width' => '100%', 'color' => $text_color, 'font-size' => '14' ) );
            }
            
            $data[] = $this->factoryobj->getRow(array(
                $sub_entry,
            ), array( 'width' => '100%' ));

            unset($sub_entry);
        }

        if ( $edit_type == 'popup' AND $edit_action_id AND !$hide_edit_icon ) {

            $onclick = new stdClass();
            $onclick->action = 'open-action';
            $onclick->action_config = $edit_action_id;
            $onclick->open_popup = 1;
            $onclick->id = 'edit-vars-' . implode('|', $var_keys);
            $onclick->sync_open = 1;
            $onclick->sync_close = 1;
            $onclick->back_button = 1;

            $data[] = $this->factoryobj->getRow(array(
                $this->factoryobj->getImage('edit-icon.png', array(
                    'width' => 20,
                    'height' => 20,
                )),
                $this->factoryobj->getText( 'Edit' )
            ), array(
                'onclick' => $onclick,
                'floating' => 1,
                'float' => 'right',
                'text-align' => 'right',
            ));
            
        }

        $result = $this->factoryobj->getRow(array(
            $this->factoryobj->getColumn($data, array(
                'background-color' => $background_color,
                'padding' => '5 6 8 6',
                'border-radius' => '2',
            )),
        ), array(
            'margin' => '5 20 5 20',
        ));

        return $result;
	}

    public function getSortedData( $data_arr ) {
        $data = array();
        $variables = array();

        foreach ($data_arr as $var_key => $label) {
            $variables[] = $var_key;

            if ( !isset($this->user_vars[$var_key]) ) {
                continue;
            }

            $entry = $this->user_vars[$var_key];

            $values = $entry;

            if ( stristr($entry, '{') ) {
                $json_result = json_decode($entry, true);
                $values = implode(', ', $json_result);
            }

            if ( $label ) {
                $data[] = $label . ': ' . $values;
            } else {
                $data[] = $values;
            }

        }

        return array(
            'data' => $data,
            'variables' => $variables,
        );
    }

}
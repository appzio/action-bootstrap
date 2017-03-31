<?php
/**
 * Created by PhpStorm.
 * User: trailo
 * Date: 11/01/17
 * Time: 16:36
 */

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleRentaltenantproperties extends ArticleComponent
{

    public function template(){
        $residenceOptions = array(
            'professional' => '{#i_am_professional#}',
            'student' => '{#i_am_a_student#}'
        );
        $radioButtonsParameters = array(
            'variable' => 'residence_status',
            'field_offset' => '3',
            'show_separator' => false,
            'clustered_mode' => false
        );

        $residenceStatusRow[] = $this->factoryobj->formkitRadiobuttons('{#residence_status#}', $residenceOptions, $radioButtonsParameters);
        $output[] = $this->factoryobj->getRow($residenceStatusRow, array(
            'style' => 'propertydetail-shadowbox-indent'
        ));

        $familyOptions = array(
            'myself' => "{#it's_just_myself#}",
            'partner' => "{#i'm_living_with_a_partner#}",
            'friends' => "{#we're_friends_sharing#}",
            'family' => "{#we're_a_family_with_kids#}"
        );

        $radioButtonsParameters = array(
            'variable' => 'family_situation',
            'field_offset' => 4,
            'show_separator' => false,
            'clustered_mode' => false,
            'row_mode' => true,
        );

        $familySituationRow[] = $this->factoryobj->formkitRadiobuttons('{#family_situation#}', $familyOptions, $radioButtonsParameters);
        $output[] = $this->factoryobj->getRow($familySituationRow, array(
            'style' => 'propertydetail-shadowbox-indent'
        ));

        return $this->factoryobj->getColumn($output);
    }

}
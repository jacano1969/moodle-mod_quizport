<?php
class quizport_output_hp_6_jmatch extends quizport_output_hp_6 {
    var $icon = 'pix/f/jmt.gif';
    var $js_object_type = 'JMatch';

    var $templatefile = 'jmatch6.ht_';
    var $templatestrings = 'PreloadImageList|QsToShow|FixedArray|DragArray';

    var $l_items = array();
    var $r_items = array();

    // Glossary autolinking settings
    var $headcontent_strings = 'CorrectResponse|IncorrectResponse|YourScoreIs|F|D';
    var $headcontent_arrays = '';

    var $response_text_fields = array(
        'correct', 'wrong' // remove: ignored
    );

    var $response_num_fields = array(
        'checks' // remove: score, weighting, hints, clues
    );

    // constructor function
    function quizport_output_hp_6_jmatch(&$quiz) {
        parent::quizport_output_hp_6($quiz);
        array_push($this->javascripts, 'mod/quizport/output/hp/6/jmatch/jmatch.js');
    }

    function fix_bodycontent() {
        // remove instructions if they are not required
        $search = '/\s*<div id="InstructionsDiv" class="StdDiv">\s*<div id="Instructions">\s*<\/div>\s*<\/div>/s';
        $this->bodycontent = preg_replace($search, '', $this->bodycontent, 1);

        parent::fix_bodycontent();
    }

    function fix_js_WriteToInstructions(&$str, $start, $length) {
        // remove this function from JMatch as it is not used
        $str = substr_replace($str, '', $start, $length);
    }

    function fix_bodycontent_DragAndDrop() {
        $search = '/for \(var i=0; i<F\.length; i\+\+\)\{.*?\}/s';
        $replace = ''
            ."var myParentNode = null;\n"
            ."if (navigator.appName=='Microsoft Internet Explorer' && (document.documentMode==null || document.documentMode<8)) {\n"
            ."	// IE8+ (compatible mode) IE7, IE6, IE5 ...\n"
            ."} else {\n"
            ."	// Firefox, Safari, Opera, IE8+\n"
            ."	var obj = document.getElementsByTagName('div');\n"
            ."	if (obj && obj.length) {\n"
            ."		myParentNode = obj[obj.length - 1].parentNode;\n"
            ."	}\n"
            ."}\n"
            ."for (var i=0; i<F.length; i++){\n"
            ."	if (myParentNode){\n"
            ."		var div = document.createElement('div');\n"
            ."		div.setAttribute('id', 'F' + i);\n"
            ."		div.setAttribute('class', 'CardStyle');\n"
            ."		myParentNode.appendChild(div);\n"
            ."	} else {\n"
            ."		document.write('".'<div id="'."F' + i + '".'" class="CardStyle"'."></div>');\n"
            ."	}\n"
            ."}"
        ;
        $this->bodycontent = preg_replace($search, $replace, $this->bodycontent, 1);

        $search = '/for \(var i=0; i<D\.length; i\+\+\)\{.*?\}/s';
        $replace = ''
            ."for (var i=0; i<D.length; i++){\n"
            ."	if (myParentNode){\n"
            ."		var div = document.createElement('div');\n"
            ."		div.setAttribute('id', 'D' + i);\n"
            ."		div.setAttribute('class', 'CardStyle');\n"
            ."		div.setAttribute('onmousedown', 'beginDrag(event, ' + i + ')');\n"
            ."		myParentNode.appendChild(div);\n"
            ."	} else {\n"
            ."		document.write('".'<div id="'."D' + i + '".'" class="CardStyle" onmousedown="'."beginDrag(event, ' + i + ')".'"'."></div>');\n"
            ."	}\n"
            ."}\n"
            ."// m = div = myParentNode = null;"
       ;
        $this->bodycontent = preg_replace($search, $replace, $this->bodycontent, 1);
    }

    function fix_js_StartUp_DragAndDrop(&$substr) {

        // fix top and left of drag area
        $this->fix_js_StartUp_DragAndDrop_DragArea($substr);

        // stretch the canvas vertically down
        if ($pos = strrpos($substr, '}')) {
            $insert = ''
            ."	var b = 0;\n"
            ."	var objParentNode = null;\n"
            ."	if (window.F && window.D) {\n"
            ."		var obj = document.getElementById('F'+(F.length-1));\n"
            ."		if (obj) {\n"
            ."			b = Math.max(b, getOffset(obj, 'Bottom'));\n"
            ."			objParentNode = objParentNode || obj.parentNode;\n"
            ."		}\n"
            ."		var obj = document.getElementById('D'+(D.length-1));\n"
            ."		if (obj) {\n"
            ."			b = Math.max(b, getOffset(obj, 'Bottom'));\n"
            ."			objParentNode = objParentNode || obj.parentNode;\n"
            ."		}\n"
            ."	}\n"
            ."	if (b) {\n"
            ."		// stretch parentNodes down vertically, if necessary\n"
            ."		var canvas = document.getElementById('middle-column');\n"
            ."		while (objParentNode) {\n"
            ."			if (b > getOffset(objParentNode, 'Bottom')) {\n"
            ."				setOffset(objParentNode, 'Bottom', b+10);\n"
            ."			}\n"
            ."			if (canvas && objParentNode==canvas) {\n"
            ."				objParentNode = null;\n"
            ."			} else {\n"
            ."				objParentNode = objParentNode.parentNode;\n"
            ."			}\n"
            ."		}\n"
            ."	}\n"
            ;
            $substr = substr_replace($substr, $insert, $pos, 0);
        }
    }

    function get_js_functionnames() {
        // start list of function names
        $names = parent::get_js_functionnames();
        $names .= ($names ? ',' : '').'CheckAnswers,beginDrag';
        return $names;
    }

    function get_stop_function_name() {
        return 'CheckAnswers';
    }

    function fix_js_beginDrag(&$str, $start, $length) {
        $substr = substr($str, $start, $length);
        if ($pos = strpos($substr, '{')) {
            $insert = "\n"
                ."	if (e && e.target && e.target.tagName && e.target.tagName.toUpperCase()=='OBJECT') {\n"
                ."		return;\n"
                ."	}\n"
            ;
            $substr = substr_replace($substr, $insert, $pos+1, 0);
        }
        $str = substr_replace($str, $substr, $start, $length);
    }

    function fix_js_StartUp_DragAndDrop_Flashcard(&$substr) {

        // fix top and left of drag area
        $this->fix_js_StartUp_DragAndDrop_DragArea($substr);

        // stretch the canvas vertically down
        if ($pos = strrpos($substr, '}')) {
            $insert = ''
            ."	var canvas = document.getElementById('middle-column');\n"
            ."	if (canvas) {\n"
            ."		var b = 0;\n"
            ."		var tbody = document.getElementById('Questions');\n"
            ."		if (tbody) {\n"
            ."			var b = getOffset(tbody.parentNode, 'Bottom');\n"
            ."			if (b){\n"
            ."				setOffset(canvas, 'Bottom', b+4);\n"
            ."			}\n"
            ."		}\n"
            ."	}\n"
            ;
            $substr = substr_replace($substr, $insert, $pos, 0);
        }
    }

    function fix_mediafilter_onload_extra_Flashcard() {
        return ''
            ."\n"
            .'	// show first item'."\n"
            .'	if (window.ShowFirstItem){'."\n"
            .'		setTimeout("ShowItem()", 2000);'."\n"
            .'	}'."\n"
            ."\n"
            .parent::fix_mediafilter_onload_extra()
        ;
    }

    function fix_js_DeleteItem_Flashcard(&$str, $start, $length) {
        $substr = ''
            ."function DeleteItem(){\n"
            ."	var Qs = document.getElementById('Questions');\n"
            ."	if (Qs) {\n"
            ."		if (CurrItem) {\n"
            ."			var DelItem = CurrItem;\n"
            ."			Stage = 2;\n"
            ."			ShowItem();\n"
            ."			Qs.removeChild(DelItem);\n"
            ."		}\n"
            ."		var count = Qs.getElementsByTagName('tr').length\n"
            ."	} else {\n"
            ."		// no Questions - shouldn't happen !!\n"
            ."		var count = 0;\n"
            ."	}\n"
            ."	if (count==0){\n"
            ."		HP.onunload(4,0);\n"
            ."	}\n"
            ."}\n"
        ;
        $str = substr_replace($str, $substr, $start, $length);
    }

    function fix_js_ShowItem_Flashcard(&$str, $start, $length) {

        $substr = substr($str, $start, $length);

        $substr = preg_replace('/(\s*)'.'return;/', '\\1'.'HP.onunload(4,0);'.'\\0', $substr);

        if ($pos = strrpos($substr, '}')) {
            $append = "\n"
                ."	var canvas = document.getElementById('middle-column');\n"
                ."	if (canvas) {\n"
                ."		var b = 0;\n"
                ."		var tbody = document.getElementById('Questions');\n"
                ."		if (tbody) {\n"
                ."			var b = getOffset(tbody.parentNode, 'Bottom');\n"
                ."			if (b){\n"
                ."				setOffset(canvas, 'Bottom', b+4);\n"
                ."			}\n"
                ."		}\n"
                ."	}\n"
                ."	HP.onclickCheck(CurrItem);\n"
            ;
            $substr = substr_replace($substr, $append, $pos, 0);
        }

        $str = substr_replace($str, $substr, $start, $length);
    }

    function fix_title_rottmeier_JMemori() {
        global $QUIZPORT;

        // extract the current title
        $search = '/(<span class="ExerciseTitle">)\s*(.*?)\s*(<\/span>)/is';
        if (preg_match($search, $this->bodycontent, $matches)) {
            $title = $this->get_title();
            if (has_capability('mod/quizport:manage', $QUIZPORT->modulecontext)) {
                $title .= $QUIZPORT->print_commands(
                    // $types, $quizportscriptname, $id, $params, $popup, $return
                    array('update'), 'editquiz.php', 'quizid',
                    array('quizid'=>$QUIZPORT->quizid, 'qnumber'=>$QUIZPORT->qnumber, 'unumber'=>$QUIZPORT->unumber),
                    false, true
                );
            }
            $replace = $matches[1].$title.$matches[3];
            $this->bodycontent = str_replace($matches[0], $replace, $this->bodycontent);
        }
    }

    function fix_js_WriteFeedback_JMemori(&$str, $start, $length) {
        $substr = substr($str, $start, $length);

        // replace code for hiding elements
        $search = '/(\s*)if \(is\.ie\){.*?}.*?}.*?}/s';
        $replace = '\\1'
            ."ShowElements(false, 'input');\\1"
            ."ShowElements(false, 'select');\\1"
            ."ShowElements(false, 'object');\\1"
            ."ShowElements(true, 'object', 'FeedbackContent');\\1"
            ."if (navigator.userAgent.indexOf('Chrome')>=0) {\\1"
            ."	ShowElements(false, 'embed');\\1"
            ."	ShowElements(true, 'embed', 'FeedbackContent');\\1"
            ."}"
        ;
        $substr = preg_replace($search, $replace, $substr, 1);

        // add ShowElements() function
        $substr = ''
            ."function ShowElements(Show, TagName, ContainerToReverse){\n"
            ."	if (ContainerToReverse) {\n"
            ."		var TopNode = document.getElementById(ContainerToReverse);\n"
            ."	} else {\n"
            ."		var TopNode = null;\n"
            ."	}\n"
            ."	if (TopNode) {\n"
            ."		var Els = TopNode.getElementsByTagName(TagName);\n"
            ."	} else {\n"
            ."		var Els = document.getElementsByTagName(TagName);\n"
            ."	}\n"
            ."	if (Show) {\n"
            ."		var v = 'visible';\n"
            ."		var d = '';\n"
            ."	} else {\n"
            ."		var v = 'hidden';\n"
            ."		var d = 'none';\n"
            ."	}\n"
            ."	for (var i=0; i<Els.length; i++){\n"
            ."		if (TagName == 'embed' || TagName == 'object') {\n"
            ."			Els[i].style.visibility = v;\n"
            ."			if (is.mac && is.ns) {\n"
            ."				Els[i].style.display = d;\n"
            ."			}\n"
            ."		} else if (is.ie && is.v < 7) {\n"
            ."			Els[i].style.visibility = v;\n"
            ."		}\n"
            ."	}\n"
            ."}\n"
            .$substr
        ;

        $str = substr_replace($str, $substr, $start, $length);
    }

    function fix_js_HideFeedback_JMemori(&$str, $start, $length) {
        $substr = substr($str, $start, $length);

        parent::fix_js_HideFeedback($substr, 0, strlen($substr));

        // replace code for showing elements
        $search = '/(\s*)if \(is\.ie\){.*?}.*?}.*?}/s';
        $replace = '\\1'
            ."ShowElements(true, 'input');\\1"
            ."ShowElements(true, 'select');\\1"
            ."ShowElements(true, 'object');\\1"
            ."ShowElements(false, 'object', 'FeedbackContent');\\1"
            ."if (navigator.userAgent.indexOf('Chrome')>=0) {\\1"
            ."	ShowElements(true, 'embed');\\1"
            ."	ShowElements(false, 'embed', 'FeedbackContent');\\1"
            ."}"
        ;
        $substr = preg_replace($search, $replace, $substr, 1);

        $str = substr_replace($str, $substr, $start, $length);
    }

    function fix_js_ShowSolution_JMemori(&$str, $start, $length) {
        $substr = substr($str, $start, $length);

        if ($this->delay3==QUIZPORT_DELAY3_AFTEROK) {
            $flag = 1; // set form values only
        } else {
            $flag = 0; // set form values and send form
        }
        $substr = str_replace('Finish()', "HP.onunload(4,$flag)", $substr);

        $str = substr_replace($str, $substr, $start, $length);
    }

    function fix_js_CheckPair_JMemori(&$str, $start, $length) {
        $substr = substr($str, $start, $length);

        // surround main body of function with if (id>=0) { ... }
        $search = '/(?<={)(.*)(?=if \(Pairs == F\.length\))/s';
        $replace = "\n\t".'if (id>=0) {\\1}'."\n\t";
        $substr = preg_replace($search, $replace, $substr, 1);

        parent::fix_js_CheckAnswers($substr, 0, strlen($substr));
        $str = substr_replace($str, $substr, $start, $length);
    }
}
?>
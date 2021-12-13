<?php
class form{
    public $attr;
    public $element;

    public function __construct(array $attr, array $element = array()){
        $this->method = (array_key_exists('method', $attr)) ? $attr["method"] : "";
        $this->action = (array_key_exists('action', $attr)) ? $attr["action"] : "";
        $this->class = (array_key_exists('class', $attr)) ? $attr["class"] : "";
        $this->element = $element;
    }

    public function setElement(string $tag, array $attribut):void{
        $element = array();
        // input, button
        $tag = trim(strtolower($tag));
        if($tag=="input" || $tag=="button" || $tag=="textarea" || "select"){
            $element["tag"] = $tag;
            $element["attributList"] = $attribut; 
            $this->element[] = $element;
        }else{
            if(PROD==false){
                trigger_error("<p class='dev_critical'>Error &laquo; $tag &raquo; : is not yet compatible...</p>", E_USER_ERROR);
            }
        }
    }

    /*
        function for display form
    */
    public function display():string{
 
        $return = "<form action='{$this->action}' method='{$this->method}' class='{$this->class}'>"; // start of the string

        foreach($this->element as $k => $attributList){
            if(array_key_exists('tag', $attributList)){
                if(array_key_exists('attributList', $attributList)){
                    if(array_key_exists('name', $attributList["attributList"])){
                        $tag = format::normalize($attributList["tag"]);
                        $attr = "";
                        if($tag=="input"){
                            foreach($attributList["attributList"] as $attribute => $attrValue){
                                $attr .= " $attribute='$attrValue'";
                            }
                            $return .= "<$tag $attr />";
                        }elseif($tag=="textara" || $tag=="button"){
                            foreach($attributList as $attribute => $attrValue){
                                if(trim(strtolower($attribute))!="value"){
                                    $attr .= " $attribute='$attrValue'";
                                }
                            }
                            $value = array_key_exists('value', $attributList) ? $attributList["value"]: "";
                            $return .= "<$tag $attr >$value</textarea>"; 
                        }elseif($tag=="select"){
                            // multiple select debug (1/2)
                            if(in_array(format::normalize("multiple"), $attributList["attributList"])){
                                if(!in_array(format::normalize("required"), $attributList["attributList"])){
                                    $attributList["attributList"]["required"] = NULL;
                                }
                            }

                            // i continue (multiple or single)
                            foreach($attributList["attributList"] as $attribute => $attrValue){
                                if(trim(strtolower($attribute))!="option"){
                                    $attr .= " $attribute='$attrValue'";
                                }
                            }
                            $optionList = "";
                            if(array_key_exists('option', $attributList["attributList"])){
                                if(gettype($attributList["attributList"]["option"])=="array"){
                                    // multiple select debug (1/2)
                                    if(in_array(format::normalize("multiple"), $attributList["attributList"])){
                                        $attributList["attributList"]["option"][NULL]="";
                                    }

                                    foreach($attributList["attributList"]["option"] as $kOption => $vOption){
                                        $optionList .= "<option value='$kOption'>$vOption</option>";
                                    }
                                }else{
                                    http_response_code(500);
                                    if(PROD==false){
                                        trigger_error("<p class='dev_critical txt-center'>Internal error: the key &quot; option &quot; for the &quot; select &quot; must be an array.</p>", E_USER_ERROR);
                                    }else{
                                        die("<p class='dev_critical txt-center'>Erreur 500: activez le mode &laquo; dev &raquo; si vous êtes l'administrateur du site pour plus d'informations.</p>");
                                    }  
                                }
                            }else{
                                
                            }
                            $return .= "<$tag $attr>$optionList</$tag>";
                        }else{
                            http_response_code(500);
                            if(PROD==false){
                                trigger_error("<p class='dev_critical txt-center'>Internal error: type of element unknown</p>", E_USER_ERROR);
                            }else{
                                die("<p class='dev_critical txt-center'>Erreur 500: activez le mode &laquo; dev &raquo; si vous êtes l'administrateur du site pour plus d'informations.</p>");
                            }
                        }
                    }else{
                        http_response_code(500);
                        if(PROD==false){
                            trigger_error("<p class='dev_critical txt-center'>Internal error: one or severals element(s) of the form has not name.</p>", E_USER_ERROR);
                        }else{
                            die("<p class='dev_critical txt-center'>Erreur 500: activez le mode &laquo; dev &raquo; si vous êtes l'administrateur du site pour plus d'informations.</p>");
                        }    
                    }
                }else{
                    http_response_code(500);
                    if(PROD==false){
                        trigger_error("<p class='dev_critical txt-center'>Internal error: any HTML attribute found for on more more elements</p>", E_USER_ERROR);
                    }else{
                        die("<p class='dev_critical txt-center'>Erreur 500: activez le mode &laquo; dev &raquo; si vous êtes l'administrateur du site pour plus d'informations.</p>");
                    }    
                }
            }else{
                http_response_code(500);
                if(PROD==false){
                    trigger_error("<p class='dev_critical txt-center'>Internal error: tag unknown</p>", E_USER_ERROR);
                }else{
                    die("<p class='dev_critical txt-center'>Erreur 500: activez le mode &laquo; dev &raquo; si vous êtes l'administrateur du site pour plus d'informations.</p>");
                }    
            }
        }
        return $return."</form>"; // end of the string
    }    

    public function check():array{
        $errorList = array();
        $methodUsed = (format::normalize($this->method)=="post") ? "POST" : "GET";
        $dataSubmit = (format::normalize($this->method)=="post") ? $_POST : $_GET;
        if(count($dataSubmit)>0){ // i check if i have data (if the form is submit)

            if(count($dataSubmit)==count($this->element)){ // check if number of parameters get/post

                // FIRST ARRAY
                $elementListNameFromObj = array(); // i create a new array for add the name of all elements form object 
                foreach($this->element as $k => $attributList){ // for each element
                    $elementListNameFromObj[] = $attributList["attributList"]["name"]; // i add in array the name of all elements from object
                }

                // SECOND ARRAY
                $elementListNameFromSubmit = array(); // array for retrieve all names for elements from submit (i don't will use array_reverse for security reasons and possible conflicts)
                foreach($dataSubmit as $kDataSubmit => $vDataSubmit){
                    $elementListNameFromSubmit[] = security::cleanStr($kDataSubmit); 
                }

                // COMPARE ARRAYS
                if(sort($elementListNameFromObj) == sort($elementListNameFromSubmit)){ // all names of the form aren't wrong (all input field names from form are expected)
                    $errorList[] = "OK";
                    foreach($this->element as $k => $attributList){
                        $tag = format::normalize($attributList["tag"]);
                        if($tag == "textarea" || $tag == "input" || $tag == "select"){
                            // CHECK IF FIELD IS REQUIRED
                            $bypassCheckLength = false;
                            if(array_key_exists('required', $attributList["attributList"])){ // i check if there the attr required in object
                                if(security::cleanStr($dataSubmit[$attributList["attributList"]["name"]])==""){
                                    $errorList[] = "Tous les champs requis ne sont pas complétés.";
                                    $bypassCheckLength = true;
                                    if(PROD==false){
                                        trigger_error("<p class='dev_critical'>One or more element required bypassed.</p>", E_USER_ERROR);
                                    }  
                                }
                            }
                            // IT'S NOT NECESSARY TO CHECK MAX/MINLENGTH IF THE REQUIRED FIELD IS EMPTY
                            if($bypassCheckLength == false){
                                // CHECK IF MAXLENGTH/MINLENGTH
                                $minORmaxLength = array("minlength", "maxlength");
                                foreach($minORmaxLength as $vMinMax){
                                    
                                    if(array_key_exists($vMinMax, $attributList["attributList"])){
                                        if(is_numeric(format::normalize($attributList["attributList"][$vMinMax]))){ // check if it's an integer
                                            
                                                if($vMinMax=="minlength"){
                                                    if(strlen(format::normalize($dataSubmit[$attributList["attributList"]["name"]])) < $attributList["attributList"][$vMinMax]){ // if data form form > maxlength
                                                        $errorList[] = "Un ou des champs ne respecte pas la longueur minimum requise.";
                                                    }
                                                }else{
                                                    if(strlen(format::normalize($dataSubmit[$attributList["attributList"]["name"]])) > $attributList["attributList"][$vMinMax]){ // if data form form > maxlength
                                                        $errorList[] = "Un ou des champs dépasse la longueur maximum.";
                                                    }
                                                }
                                        }else{
                                            $errorList[] = "Erreur interne.";
                                            if(PROD==false){
                                                trigger_error("<p class='dev_critical'>$vMinMax MUST BE an integer.</p>", E_USER_ERROR);
                                            }  
                                        }
                                    }
                                }
                            }

                            // CHECK OUT IF INPUT TYPE IS NOT WRONG
                            if($tag == "input"){
                                if(array_key_exists('type', $attributList["attributList"])){
                                    if($attributList["attributList"]["type"]=="email"){
                                        if (!filter_var($dataSubmit[$attributList["attributList"]["name"]], FILTER_VALIDATE_EMAIL)) {
                                            $errorList[] = "Un ou des champs e-mail invalide(s): vérifiez le format.";
                                        }
                                    }elseif($attributList["attributList"]["type"]=="number" || $attributList["attributList"]["type"]=="range"){
                                        if(!is_numeric($dataSubmit[$attributList["attributList"]["name"]])){
                                            $errorList[] = "Un ou des champs incorrect(s): une valeur numérique est attendue.";
                                        }else{
                                            // IF IS THE VALUE IS NUMERIC
                                            // attr: min
                                            if(array_key_exists("min", $attributList["attributList"])){ // if the attribute "min" is init in object
                                                if(is_numeric($attributList["attributList"]["min"])){
                                                    if(intval($dataSubmit[$attributList["attributList"]["name"]]) < intval($attributList["attributList"]["min"])){
                                                        $errorList[] = "Un ou des champs incorrect(s): une valeur numérique est inférieur à celle attendue.";
                                                    }
                                                }else{
                                                    $errorList[] = "Erreur interne.";
                                                    if(PROD==false){
                                                        trigger_error("<p class='dev_critical'>Check out if the attribute &quot, min &quot; is a numeric value.</p>", E_USER_ERROR);
                                                    }    
                                                }
                                            }
                                            // attr: max
                                            if(array_key_exists("max", $attributList["attributList"])){ // if the attribute "min" is init in object
                                                if(is_numeric($attributList["attributList"]["max"])){
                                                    if(intval($dataSubmit[$attributList["attributList"]["name"]]) > intval($attributList["attributList"]["max"])){
                                                        $errorList[] = "Un ou des champs incorrect(s): une valeur numérique est supérieure à celle attendue.";
                                                    }
                                                }else{
                                                    $errorList[] = "Erreur interne.";
                                                    if(PROD==false){
                                                        trigger_error("<p class='dev_critical'>Check out if the attribute &quot, min &quot; is a numeric value.</p>", E_USER_ERROR);
                                                    }    
                                                }
                                            }     
                                        }
                                    }elseif($attributList["attributList"]["type"]=="color"){
                                        if(!preg_match('/^#[a-f0-9]{6}$/i', $dataSubmit[$attributList["attributList"]["name"]])){
                                            $errorList[] = "Un ou plusieur(s) champ(s) couleur HEX invalides.";
                                            if(PROD==false){
                                                trigger_error("<p class='dev_critical'>One ore more attribute(s) &quot; type &quot; missing in the tag &quot; input &quot;.</p>", E_USER_ERROR);
                                            }         
                                        }
                                    }
                                }else{
                                    $errorList[] = "Erreur interne.";
                                    if(PROD==false){
                                        trigger_error("<p class='dev_critical'>One ore more attribute(s) &quot; type &quot; missing in the tag &quot; input &quot;.</p>", E_USER_ERROR);
                                    }     
                                }
                            }

                            // IF SELECT
                            if($tag=="select"){
                                if(array_key_exists('option', $attributList["attributList"])){
                                    if(gettype($attributList["attributList"]["option"])=="array"){ // i check if the option value provided is of type "array"
                                        if(array_key_exists("multiple", $attributList["attributList"])){ // IF SELECT MULTIPLE EXPECTED
                                            // MULTIPLE VALUES RETURNED
                                            if(gettype($dataSubmit[$attributList["attributList"]["name"]])=="array"){
                                                $cleanArr = format::cleanArr($dataSubmit[$attributList["attributList"]["name"]]);
                                                if(count($cleanArr)>0){
                                                    foreach($cleanArr as $value){
                                                        if(!array_key_exists($value, $attributList["attributList"]["option"])){
                                                            // <!------------------------------------------
                                                                // --> supprimer doublons dans le tableau...
                                                                // ONLY ALL VALUES ARE NULL (SECURITY PREVENT)
                                                            // ------------------------------------------!>
                                                            $errorList[] = "Erreur: valeur(s) non-attendu(s) d'un ou plusieurs menu déroulants ";
                                                            if(PROD==false){
                                                                trigger_error("<p class='dev_critical'>Security: the value sended form &quot; select &quot; dont't feel be in the object.</p>", E_USER_ERROR);
                                                            }   
                                                        }
                                                    }
                                                }else{
                                                    $errorList[] = "Erreur: valeur(s) non-attendu(s) d'un ou plusieurs menu déroulants.";
                                                }
                                            }else{
                                                // IF ALONE VALUE RETURNED
                                                if(!array_key_exists($dataSubmit[$attributList["attributList"]["name"]], $attributList["attributList"]["option"])){ // i check if the value sended is in array (object)
                                                    // <!------------------------------------------
                                                            // RETURN ERROR IF ONLY INPUT REQUIRED AND 1 VALUE NULL
                                                    // ------------------------------------------!>
                                                    if(array_key_exists("required", $attributList["attributList"])){
                                                        $errorList[] = "Erreur: valeur(s) non-attendu(s) d'un ou plusieurs menu déroulants ";
                                                        if(PROD==false){
                                                            trigger_error("<p class='dev_critical'>Security: the value sended form &quot; select &quot; dont't feel be in the object.</p>", E_USER_ERROR);
                                                        }   
                                                    }
                                                }
                                            }
                                        }else{
                                            // IF ALONE VALUE EXPECTED
                                            if(gettype($dataSubmit[$attributList["attributList"]["name"]])=="string"){
                                                if(!array_key_exists($dataSubmit[$attributList["attributList"]["name"]], $attributList["attributList"]["option"])){ // i check if the value sended is in array (object)
                                                    // <!------------------------------------------
                                                        // NOTHING ELSE HERE
                                                    // ------------------------------------------!>
                                                    $errorList[] = "Erreur: valeur(s) non-attendu(s) d'un ou plusieurs menu déroulants ";
                                                    if(PROD==false){
                                                        trigger_error("<p class='dev_critical'>Security: the value sended form &quot; select &quot; dont't feel be in the object.</p>", E_USER_ERROR);
                                                    }   
                                                }
                                            }else{
                                                $errorList[] = "Erreur: une seule valeur attendue pour un ou plusieurs menu déroulant.";
                                                if(PROD==false){
                                                    trigger_error("<p class='dev_critical'>Security: string expected for &quot; select &quot; field.</p>", E_USER_ERROR);
                                                }   
                                            }
                                        }
                                    }else{
                                        $errorList[] = "Erreur interne.";
                                        if(PROD==false){
                                            trigger_error("<p class='dev_critical'>Check out the element(s) &quot; select &quot;: value of type array expected.</p>", E_USER_ERROR);
                                        }   
                                    }
                                }else{
                                    $errorList[] = "Erreur interne.";
                                    if(PROD==false){
                                        trigger_error("<p class='dev_critical'>Check out the element(s) &quot; select &quot;: a dropdown must contain an array with value(s).</p>", E_USER_ERROR);
                                    }   
                                }
                            }
                        }else{
                            $errorList[] = "Erreur interne.";
                            if(PROD==false){
                                trigger_error("<p class='dev_critical'>Unrecognized form element (tag).</p>", E_USER_ERROR);
                            }  
                        }
                    }
                }else{
                    $errorList[] = "&Eacute;lements manquant ou en trop.";
                    if(PROD==false){
                        trigger_error("<p class='dev_critical'>Check if all submitted data $methodUsed is expected (that there is no more data sent).</p>", E_USER_ERROR);
                    }  
                }
                
            }else{
                $errorList[] = "Element de formulaire manquant.";
                if(PROD==false){
                    var_dump(count($dataSubmit));
                    var_dump(count($this->element));
                    trigger_error("<p class='dev_critical'>Check that all the elements of the form have an attribute &laquo; name &raquo;</p>", E_USER_ERROR);
                }
            }
        }else{
            $errorList[] = "Pas de données envoyées"; 
        }
        return $errorList;
    }
}
?>

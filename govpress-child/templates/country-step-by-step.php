<?php
/**
 * Template Name: Country step
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
get_header(); 
$branch = 'master';
if(isset($_GET['country'])){
    $branch = $_GET['country'];
    $key_country = $_GET['country'];
}else{
    $key_country = get_post_meta( get_the_ID(), 'country', true );
}

                
?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
        <script>
            var inputs;
            var actualInput;
            function submit(){

                $(".alert").hide();
                if($("#"+actualInput).val() != ""){
                    var responses = new Object();
                    var errors = [];

                    
                    for(var i = 0; i < inputs.length; i++){
                        $("#"+inputs[i]).hide;
                        if($("#"+inputs[i]).val() != ""){
                            var response = $("#"+inputs[i]).val();
                            eval("responses."+inputs[i]+" = response;");
                        }
                        var trad = getTraduction($("#langues").val(), inputs[i]);
                        errors[inputs[i]] = trad+' missing';
                    }

                    responses = JSON.stringify(responses);
                    var result = walk($( "#typeOfWork" ).val(), responses, 'master', $("#langues").val());

                    if(result.error == 3){
                        $("#labelWarning").html(getTraduction($("#langues").val(), 'labelWarning'));
                        $("#error").html(getTraduction($("#langues").val(), 'labelResponseIsNotANumber'));
                        $(".alert-warning").show();
                    }
                    else{
                        if(result.error == 1){
                            $(".questionInput").prop("disabled","disabled");
                            newQuestion(result.waiting_response);
                            
                        }else{
                            $("#result").html(result);
                            $(".alert-success").show();
                            $(".questionInput").prop("disabled","disabled");
                            <?php
                            if($branch == 'master'){
                            ?>
                                _gaq.push(['_trackEvent', 'Calculators', "<?php echo $key_country; ?>", $("#langues").val()]);
                            <?php 
                            } 
                            ?>
                        }
                    }
                    
                    $("#next").hide();
                }
                
            }

            function reset(){
                $("#next").hide();
                $("#reset").hide();
                $(".alert-success").hide();
                $("#forms").html("");
                $("#typeOfWork").val("");
                $( "#langues" ).removeAttr("disabled","");
            }

            $(document).on('change','.questionInput',function(){
                changeInput();
            });

            

            function changeInput(){
                if($("#"+actualInput).val() != ""){
                    $("#next").show();
                }else{
                    $("#next").hide();
                }
            }
            
            $(document).on('change','#typeOfWork',function(){
                $("#forms").html("");

                if($("#typeOfWork").val() != ""){
                    $("#langues").prop("disabled", "disabled");
                }else{
                    $( "#langues" ).removeAttr("disabled","");
                }

                if($( "#typeOfWork" ).val() != ""){
                    getData("<?php echo $key_country; ?>", $("#langues").val(), function( dataTrad ) {
                        traductionData = dataTrad;
                        inputs = getInputs($( "#typeOfWork" ).val());
                        if(inputs.length > 0){
                            newQuestion(inputs[0]);
                        }
                        $("#reset").show();
                    });
                }else{
                    reset();
                }
                
              });   

            $(function() {
                
                $("#secondary").hide();
                getData("<?php echo $key_country; ?>", "<?php echo $key_country; ?>", function( data ) {
                    file = data;



                    //getInputs(id_subgraph);

                    var listSubgraph = getListSubgraph();

                    for(var i = 0; i < listSubgraph.length; i++){
                        $('#typeOfWork')
                             .append($("<option></option>")
                             .attr("value",i)
                             .text(listSubgraph[i])); 
                    }

                    var lang = getUrlVars()["lang"];
                    if(lang !== undefined){
                        lang = lang.toUpperCase();
                    }else{
                        lang = 'EN';
                    }

                    for(var i = 0; i < file.language.length; i++){
                        $('#langues')
                             .append($("<option></option>")
                             .attr("value",file.language[i].toUpperCase())
                             .text(file.language[i].toUpperCase())); 
                    }
                    if($('#langues').find('option[value="'+lang+'"]').length > 0){
                        $('#langues').find('option[value="'+lang+'"]').prop('selected', true); 
                    }else{
                        $('#langues').find('option[value="'+file.default_language.toUpperCase()+'"]').prop('selected', true); 
                    }
                    
                    changeLangue();
                });

                $('.close').click(function(){
                    $(".alert").hide();
                });
            });

            $(document).on('change','#langues',function(){
                if($("#langues").val() != ""){
                    $("#waitingPart").show();
                    $("#calculatorPart").hide();
                    changeLangue();
                }
            });

            $('body').on('click', function (e) {
                $('[data-toggle="popover"]').each(function () {
                    //the 'is' for buttons that trigger popups
                    //the 'has' for icons within a button that triggers a popup
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });

            function changeLangue(){
                
                getData("<?php echo $key_country; ?>", $("#langues").val(), function( dataTrad ) {
                    traductionData = dataTrad;
                    $("#labelTypeOfWork").text(getTraduction($("#langues").val(), 'labelTypeOfWork'));
                    $("#labelLangue").text(getTraduction($("#langues").val(), 'labelLangue'));
                    $("#waitingPart").hide();
                    $("#calculatorPart").show();
                });
                
            }

            function newQuestion(input){
                var trad = getTraduction($("#langues").val(), 'question_'+input);
                var datapoint = getResponseById(input);
                var inputHTML= "";
                var helpText = "";
                actualInput = input;
                var tradHint = getTraduction($("#langues").val(),'hint_'+datapoint.id)
                if(tradHint != ""){
                    helpText = '<a href="#" class="popoverInfo" data-toggle="popover" title="Additional information" placement="bottom" data-content="'+tradHint+'"><i class="fa fa-question-circle fa-lg" style=" padding-top: 10px; "></i></a>';
                }
                switch(datapoint.type) {
                    case 'numeric':
                        inputHTML = '<input type="text" class="col-sm-12 questionInput" name="'+input+'" id="'+input+'" placeholder="'+trad+'" onkeypress="changeInput()"/>';
                        break;
                    case 'list':
                        inputHTML = '<select name="'+input+'" id="'+input+'" class="form-control questionInput" style=" margin-bottom: 15px; ">'
                        inputHTML += '<option value="">Select a response</option>';
                        for(var j = 0; j < datapoint.set.length; j++){
                            inputHTML += '<option value="'+datapoint.set[j]+'">'+getTraduction($("#langues").val(), datapoint.set[j])+'</option>';
                        }
                        inputHTML += '</select>';
                        break;
                    default:
                        inputHTML = '';
                } 

                $("#forms").append('\
                    <div class="row margin-top questionPart">\
                        <div class="col-sm-4">\
                            <label for="'+input+'" class="control-label">'+trad+'</label>\
                        </div>\
                        <div class="col-sm-7">\
                            '+inputHTML+'\
                        </div>\
                        <div class="col-sm-1">\
                            '+helpText+'\
                        </div>\
                    </div>\
                    ');
                $('.popoverInfo').popover({placement:'bottom'});

                $("#next").hide();
            }

            //Récupération des éléments get de l'URL
            function getUrlVars() {
                var vars = {};
                var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                    vars[key] = value.replace("#", "");;
                });
                return vars;
            }

            function getData(country, file, cb){
                if("<?php echo $branch; ?>"== "master"){
                    $.post( "/node", { country: country, name: file+".json", action: 'read', branch: 'master' } )
                    .done(function( data ) {
                        cb(data);
                    });
                }else{
                    $.post( "/node", { country: country, name: file+".json", action: 'read' } )
                    .done(function(data)
                    {
                        cb(data);
                    });
                }
            }

        </script>
        <h1 style="font-size: 25px;"><?php echo $key_country; ?></h1>
        <br/>
        <h2>Calculate</h2>
        <br/>
        <div class="row margin-left margin-top margin-bottom margin-right" id="calculatorPart" style="display:none;">
            <div class="row">
                <div class="col-sm-4">
                    <label class="control-label" id="labelLangue">Langue : </label>
                </div>
                <div class="col-sm-8">
                    <select name="langues" id="langues" class="form-control">
                        <option value="">Select a language</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <label class="control-label" id="labelTypeOfWork">Type of work : </label>
                </div>
                <div class="col-sm-8">
                    <select name="typeOfWork" id="typeOfWork" class="form-control">
                        <option value="">Select type of work</option>
                    </select>
                </div>
            </div>
            <div id="forms">
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <input type="button" id="reset" name="submit" value="Reset" class="btn btn-primary" onclick="reset();" style="display:none;" />
                </div>
                <div class="col-sm-2">
                    <input type="button" id="next" name="submit" value="Next" class="btn btn-primary" onclick="submit();" style="display:none;" />
                </div>
            </div>
            <div class="alert alert-dismissable alert-success margin-top" style="display:none;">
                <p id="result"></p>
            </div>
            <div class="alert alert-dismissable alert-danger margin-top" style="display:none;">
                <p id="resultdanger"></p>
            </div>
            <div class="alert alert-dismissable alert-warning margin-top" style="display:none;">
                <h4 id="labelWarning"></h4>
                <p id="error"></p>
            </div>
        </div>
        <div id="waitingPart" style=" margin-left: auto; margin-right: auto; width: 32px; ">
            <img src="/wp-content/themes/govpress-child/lib/pictures/ajax-loader.gif">
        </div>
        <?php
        if (have_posts()) :
           while (have_posts()) :
              the_post();
                 the_content();
           endwhile;
        endif;
        ?>
		</div><!-- #content -->
       
	</div><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();

<style>
[uib-tooltip-popup].tooltip.top-left > .tooltip-arrow,[uib-tooltip-popup].tooltip.top-right > .tooltip-arrow,[uib-tooltip-popup].tooltip.bottom-left > .tooltip-arrow,[uib-tooltip-popup].tooltip.bottom-right > .tooltip-arrow,[uib-tooltip-popup].tooltip.left-top > .tooltip-arrow,[uib-tooltip-popup].tooltip.left-bottom > .tooltip-arrow,[uib-tooltip-popup].tooltip.right-top > .tooltip-arrow,[uib-tooltip-popup].tooltip.right-bottom > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.top-left > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.top-right > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.bottom-left > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.bottom-right > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.left-top > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.left-bottom > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.right-top > .tooltip-arrow,[uib-tooltip-html-popup].tooltip.right-bottom > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.top-left > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.top-right > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.bottom-left > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.bottom-right > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.left-top > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.left-bottom > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.right-top > .tooltip-arrow,[uib-tooltip-template-popup].tooltip.right-bottom > .tooltip-arrow,[uib-popover-popup].popover.top-left > .arrow,[uib-popover-popup].popover.top-right > .arrow,[uib-popover-popup].popover.bottom-left > .arrow,[uib-popover-popup].popover.bottom-right > .arrow,[uib-popover-popup].popover.left-top > .arrow,[uib-popover-popup].popover.left-bottom > .arrow,[uib-popover-popup].popover.right-top > .arrow,[uib-popover-popup].popover.right-bottom > .arrow,[uib-popover-html-popup].popover.top-left > .arrow,[uib-popover-html-popup].popover.top-right > .arrow,[uib-popover-html-popup].popover.bottom-left > .arrow,[uib-popover-html-popup].popover.bottom-right > .arrow,[uib-popover-html-popup].popover.left-top > .arrow,[uib-popover-html-popup].popover.left-bottom > .arrow,[uib-popover-html-popup].popover.right-top > .arrow,[uib-popover-html-popup].popover.right-bottom > .arrow,[uib-popover-template-popup].popover.top-left > .arrow,[uib-popover-template-popup].popover.top-right > .arrow,[uib-popover-template-popup].popover.bottom-left > .arrow,[uib-popover-template-popup].popover.bottom-right > .arrow,[uib-popover-template-popup].popover.left-top > .arrow,[uib-popover-template-popup].popover.left-bottom > .arrow,[uib-popover-template-popup].popover.right-top > .arrow,[uib-popover-template-popup].popover.right-bottom > .arrow{top:auto;bottom:auto;left:auto;right:auto;margin:0;}[uib-popover-popup].popover,[uib-popover-html-popup].popover,[uib-popover-template-popup].popover{display:block !important;}
</style>
  <div class="row  p-0 p-md-4 justify-content-center">
    <div class="col-12 col-md-10 col-xl-6 mt-1 pt-3 pb-3 form-color">

      <div class="container">
        <h3><?= $formTitle ?></h3>
        <hr>
        <?php if (session()->get('success')): ?>
          <div class="alert alert-success" role="alert">
            <?= session()->get('success') ?>
          </div>
        <?php endif; ?>
        <!-- Submit action -->
        <form class="" action="/risk-assessment/<?= $action ?>" method="post">
          <div class="row">
            <?php if (isset($validation)): ?>
              <div class="col-12">
                <div class="alert alert-danger" role="alert">
                  <?= $validation->listErrors() ?>
                </div>
              </div>
            <?php endif; ?>

            <div class="col-6">
              <div class="form-group" readonly="readonly" id="risk_type_selection">
              <label class = "font-weight-bold text-muted" for="project">Project</label>
               <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="project" id="project">
               <option value="" disabled <?= (isset($member['project_id']) && ($member['project_id'] != 0) ) ? '' : 'selected' ?>>
                    Select Project
                </option>
                <?php foreach ($projects as $key=>$value): ?>
                  <option 
                    <?= isset($member['project_id']) ? (($member['project_id'] == $key) ? 'selected': '') : '' ?>
                    value="<?=  $key ?>" ><?=  $value ?></option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>

            <div class="col-6">
              <div class="form-group" id="risk_name">
              <label class = "font-weight-bold text-muted" for="risk_type">Risk Type</label>
                  <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="risk_type" id="risk_type" onchange="toggleVulnerability()">
                    <option value="" disabled <?= isset($member['risk_type']) ? '' : 'selected' ?>>
                        Select Risk
                    </option>
                    <?php foreach ($riskCategory as $list): ?>
                          <option 
                            <?= isset($member['risk_type']) ? (($member['risk_type'] == $list["value"]) ? 'selected': '') : '' ?>
                            value="<?=  $list["value"] ?>" ><?=  $list["value"] ?></option>
                    <?php endforeach; ?>                    
                  </select>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
              <label class = "font-weight-bold text-muted" for="risk">Risk</label>
              <input type="text" class="form-control" name="risk" id="risk"
              value="<?= isset($member['risk']) ? htmlentities($member['risk']) : '' ?>" >
              </div>
            </div>

            <div><input type="hidden"  id="form-status" value="<?= $isEdit; ?>" /></div>
            <div class="col-12">
              <div class="form-group" id='risk_description'>
                <label class = "font-weight-bold text-muted" for="description" >Description</label>
                <textarea class="form-control" name="description" id="description"><?=
                isset($member['description']) ? trim($member['description']) : ''
                ?></textarea>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group" id='risk_description_soup'>
                <label class = "font-weight-bold text-muted" for="description-soup" >Description</label>
                <textarea class="form-control" name="description-soup" id="description-soup"><?=
                isset($member['description-soup']) ? trim($member['description-soup']) : ''
                ?></textarea>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group" id='risk_failure_mode_scope'>
                <label class = "font-weight-bold text-muted" for="failure_mode" >Failure Mode</label>
                <textarea class="form-control" name="failure_mode" id="failure_mode"><?=
                isset($member['failure_mode']) ? trim($member['failure_mode']) : ''
                ?></textarea>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group" id='risk_harm_scope'>
                <label class = "font-weight-bold text-muted" for="harm" >Harm</label>
                <textarea class="form-control" name="harm" id="harm"><?=
                isset($member['harm']) ? trim($member['harm']) : ''
                ?></textarea>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group" id='risk_cascade_effect_scope'>
                <label class = "font-weight-bold text-muted" for="cascade_effect" >Cascade Effect</label>
                <textarea class="form-control" name="cascade_effect" id="cascade_effect"><?=
                isset($member['cascade_effect']) ? trim($member['cascade_effect']) : ''
                ?></textarea>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group" id='risk_hazard_analysis'>
              <label class = "font-weight-bold text-muted" for="hazard-analysis" >Hazard Analysis & Mitigation</label>
                <textarea class="form-control" name="hazard-analysis" id="hazard-analysis" ><?=
                isset($member['hazard-analysis']) ? trim($member['hazard-analysis']) : ''
                ?></textarea>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group" id='risk_hazard_analysis_soup'>
              <label class = "font-weight-bold text-muted" for="hazard-analysis-soup" >Hazard Analysis & Mitigation</label>
                <textarea class="form-control" name="hazard-analysis-soup" id="hazard-analysis-soup" ><?=
                isset($member['hazard-analysis-soup']) ? trim($member['hazard-analysis-soup']) : ''
                ?></textarea>
              </div>
            </div>

            <div class="col-12" id="data-open-issue-soup-matrix">
              <?php foreach ($fmeaList as $key=>$value): ?>
                <div >
                  <?php if (($value['id']) < 4 ): ?>
                    <div class="form-group">
                      <label class = "font-weight-bold text-muted" ><?php echo $value['category'];?></label>
                      <br/>
                      <div class="btn-group btn-group-toggle btn-security-toggle" id="listblock<?php echo $key;?>" >
                        <?php foreach ($value['options'] as $key1=>$value1): ?>
                          <div class="btn <?php echo (($value['value']) ==  $value1['title'])? "btn-primary" : "btn-secondary"; ?> " 
                            id="RDanchor<?php echo $key;echo$key1;?>" title="<?php echo $value1['description'];?>" onclick="calculateRPNValue(<?php echo $key;?> ,<?php echo $key1;?>)">
                            <input type="radio" name="<?php echo $value['category'];?>-status-type"  
                            value="<?php echo $value1['value'].'/'.$value1['title'];?>"  <?php echo (($value['value']) ==  $value1['title'])? "checked" : ""; ?> /> <?php echo $value1['title'];?>
                          </div>
                          &nbsp;
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="col-12 col-sm-6 mt-3" id="data-open-issue-soup-rpn-matrix">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="rpn">Risk Priority Number (RPN)</label>
                <input type="text" class="form-control" name="rpn" id="rpn" readonly
                value="<?= isset($member['baseScore_severity']) ? $member['baseScore_severity'] : '' ?>" >
              </div>
            </div>

            <div class="col-12" id="data-vulnerability-matrix">
            <h4><label class = "font-weight-bold text-muted">CVSS 3.1 Base Risk Assessment</label></h4>
              <div class="row">
              <?php $count=0; foreach ($cvssList as $key=>$value): $count++;?>
                  <div class="col-<?= ($count == 1) ? '8' : '4' ?>">
                    <div class="form-group">
                    <?php if($key !='Score'): ?>
                      <div class="row">
                        <div class="col-12">
                          <label class = "font-weight-bold text-muted" ><h4><?php echo $key; ?></h4></label>
                        </div>
                      </div>
                        <?php foreach ($value as $key1=>$value1): ?>
                            <div class="col-12">
                              <div class="form-group">
                                <div class="row">
                                 <div class="col-12">
                                    <label class = "font-weight-bold text-muted" ><?php echo $value1['category']; ?></label>
                                  </div>
                                </div>
                          
                                <div class="btn-group btn-group-toggle btn-vulnerability-toggle" id="vulnerability<?php echo str_replace(' ', '', $value1['category']);?>" >
                                    <?php foreach ($value1['options'] as $key2=>$value2):?>
                                        <div class="btn <?php echo (($value1['value']) ==  $value2['title'])? "btn-primary" : "btn-secondary"; ?> "  <?php echo $key2;?>
                                          id="matrixAnchor<?php echo str_replace(' ', '', $value1['category']);echo $key2;?>" title="<?php echo $value2['description'];?>" onclick="toggleVulnerabilityTabs('<?php echo str_replace(' ', '', $value1['category']);?>', <?php echo $key2;?>)">
                                              <input type="radio" name="<?php echo str_replace(' ', '', $value1['category']);?>-status-type" class="<?php echo str_replace(' ', '', $value1['category']);?>-status-type"
                                              value="<?php echo $value2['value'].'/'.$value2['title'];?>" <?php echo (($value1['value']) ==  $value2['title'])? "checked" : ""; ?> /> <?php echo $value2['title'];?>
                                        </div>
                                        &nbsp;
                                    <?php endforeach?>
                                </div>
                              </div>
                            </div>
                            
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  </div>
              <?php endforeach; ?>
              </div>
            </div>

            <div class="col-12 col-sm-6 mt-3" id="data-vulnerability-baseScore-matrix">
              <div class="form-group">
                <label class = "font-weight-bold text-muted" for="baseScore">Base Score</label>
                <input type="text" class="form-control" name="baseScore" id="baseScore" readonly
                value="<?= (isset($member['baseScore_severity']) && $member['baseScore_severity'] !=0 ) ? $member['baseScore_severity'] : '' ?>" >
              </div>
            </div>

          
          
            <div class="col-12 col-sm-6 mt-3">
                <div class="form-group">
                <label class = "font-weight-bold text-muted" for="status">Status</label>
                    <select class="form-control  selectpicker" data-live-search="true" data-size="8" name="status" id="status">
                      <option value="" disabled <?= isset($member['status']) ? '' : 'selected' ?>>
                          Select
                      </option>
                      <?php foreach ($riskStatus as $value): ?>
                        <option 
                          <?= isset($member['status']) ? (($member['status'] == $value) ? 'selected': '') : '' ?>
                          value="<?=  $value ?>" ><?=  $value ?></option>
                      <?php endforeach; ?>
                    </select>
                </div>
            </div>
         

         <div class="col-12  mt-3">
          <div class="row justify-content-center">
              <div class="col-2">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </div>
         </div>
       

        </form>
      </div>
    </div>
  </div>

<script>
$(document).ready(function(){
  toggleVulnerability();
});

function calculateRPNValue(id, id1) {
  //removeing the all primary class and checked type and  added secondary class
  $('#listblock'+id +' div').removeClass('btn-primary').addClass('btn-secondary');
  $('#listblock'+id +' input').removeAttr('checked');
  //adding primary class to selected one
  var idVal = "#RDanchor"+id+id1;
  $(idVal).removeClass("btn-secondary").addClass('btn-primary');
  //calculating the rpn and adding checked attribute to get the values in controller
  var activeList = $('.btn-security-toggle .btn-primary input');
  var rpn=1;
  for(var i=0; i<activeList.length; i++){
      console.log("li:", activeList[i], $(activeList[i]).val());
      $(activeList[i]).attr('checked', true);
      rpn = rpn * ($(activeList[i]).val()).split('/')[0];
  }
  $('#rpn').val(rpn);
}

function toggleVulnerabilityTabs(id, id1) {
  $('#vulnerability'+id +' div').removeClass('btn-primary').addClass('btn-secondary');
  $('#vulnerability'+id +' input').removeAttr('checked');
  var idVal = "#matrixAnchor"+id+id1;
  $(idVal).removeClass("btn-secondary").addClass('btn-primary');
  var activeList = $('.btn-vulnerability-toggle .btn-primary input');
  var rpn=1;
  var postDataClaMatrix = {
				'AttackVector': '','AttackComplexity':'','PrivilegesRequired':'','UserInteraction':'', 'Scope':'',
				'ConfidentialityImpact':'', 'IntegrityImpact':'','AvailabilityImpact':''
  };
  var PR_Changed_Data = {'None':0.85, "Low":0.68, "High":0.5}; 
  for(var i=0; i<activeList.length; i++){
      $(activeList[i]).attr('checked', true);
      var scopeName = ($(activeList[i]).val()).split('/')[1]
        
        var selName = ($(activeList[i]).attr('name')).replace('-status-type', '');
        var selNameVal  = ($(activeList[i]).val()).split('/')[0];
        postDataClaMatrix[selName] = selNameVal;

        //#Checking the PR values based on the selected SCOPE
        if($(activeList[i]).attr('name') == 'Scope-status-type' && scopeName == 'Changed'){
          var PRV = $('input[name=PrivilegesRequired-status-type]:checked').val();
          var NLW = (PRV !='' && PRV != undefined) ? (PRV.split('/')[1]) : '';
          postDataClaMatrix['PrivilegesRequired'] = PR_Changed_Data[NLW];
        }
  }
  if($('input[name=Scope-status-type]:checked').val() != undefined){
    var scopeAttr = ($('input[name=Scope-status-type]:checked').val()).split('/')[1];
    calculateBaseScore(postDataClaMatrix, scopeAttr);
  }
}

function toggleVulnerability() {
  var selVal = $("#risk_type").val();
  console.log("selVal:", selVal);
    //Toggle text-area boxes based on the category selection
    $('#risk_hazard_analysis, #risk_failure_mode_scope, #risk_harm_scope, #risk_cascade_effect_scope').css('display', 'none');
  if(selVal == 'SOUP'){
      //If its Edit-form no need to change the description changes, Else display soup boxes..
      if($('#form-status').val() == true){
        $('#risk_description, #risk_hazard_analysis').css('display', 'block');
        $('#risk_description_soup, #risk_hazard_analysis_soup').css('display', 'none');
      }else{
        $('#risk_description, #risk_hazard_analysis').css('display', 'none');
        $('#risk_description_soup, #risk_hazard_analysis_soup').css('display', 'block');
      }
  }else if(selVal == 'Scope-Items'){
      console.log("display scope items");
      $('#risk_hazard_analysis, #risk_failure_mode_scope, #risk_harm_scope, #risk_cascade_effect_scope').css('display', 'block');
      $('#risk_description, #risk_description_soup, #risk_hazard_analysis_soup').css('display', 'none');
  }else{
      $('#risk_description, #risk_hazard_analysis').css('display', 'block');
      $('#risk_description_soup, #risk_hazard_analysis_soup').css('display', 'none');
  }
  if(selVal != 'Vulnerability' ) {
    $('#data-open-issue-soup-matrix, #data-open-issue-soup-rpn-matrix').css('display', 'block');
    $('#data-vulnerability-matrix, #data-vulnerability-baseScore-matrix').css('display', 'none');
  } else if(selVal=='Vulnerability'){
    $('#data-open-issue-soup-matrix, #data-open-issue-soup-rpn-matrix').css('display', 'none');
    $('#data-vulnerability-matrix, #data-vulnerability-baseScore-matrix').css('display', 'block');
  }else{
    $('#data-vulnerability-matrix, #data-vulnerability-baseScore-matrix').css('display', 'none');
  }
}

function calculateBaseScore(data, scopeAt){
  var CVSS_exploitabilityCoefficient = 8.22;
  var CVSS_scopeCoefficient = 1.08;
  var baseScore;
  var impactSubScore;
  var exploitabalitySubScore = CVSS_exploitabilityCoefficient * data['AttackVector'] * data['AttackComplexity'] * data['PrivilegesRequired'] * data['UserInteraction'];
  var impactSubScoreMultiplier = (1 - ((1 - data['ConfidentialityImpact']) * (1 - data['IntegrityImpact']) * (1 - data['AvailabilityImpact'])));
  if (scopeAt === 'Unchanged') {
    impactSubScore = data['Scope'] * impactSubScoreMultiplier;
  } else {
    impactSubScore = data['Scope'] * (impactSubScoreMultiplier - 0.029) - 3.25 * Math.pow(impactSubScoreMultiplier - 0.02, 15);
  }
  if (impactSubScore <= 0) {
    baseScore = 0;
  } else {
    if (scopeAt === 'Unchanged') {
      baseScore = CVSSroundUp1(Math.min((exploitabalitySubScore + impactSubScore), 10));
    } else {
      baseScore = CVSSroundUp1(Math.min((exploitabalitySubScore + impactSubScore) * CVSS_scopeCoefficient, 10));
    }
  }
  $('#baseScore').val(baseScore);
}
function CVSSroundUp1(d){
  return Math.ceil (d * 10) / 10;
}

</script>

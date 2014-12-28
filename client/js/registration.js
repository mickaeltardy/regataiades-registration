	function RegistrationCtrl($rootScope, $scope, $http) {

	$scope.dinnerPrice = 20;

	$scope.registration = new Object();
	
	$scope.language = "fr";
	
	$scope.boats = ["W4x", "M4x", "W8+", "M8+"];
	
	$scope.notifications = new Array();
    

	$rootScope.messagesStore = new Object();

	$http.get('../shared/data/messages.en.json').success(function(data) {
		$rootScope.messagesStore.en = data;
	});
	$http.get('../shared/data/messages.fr.json').success(function(data) {
		$rootScope.messagesStore.fr = data;
		$scope.changeLanguage($scope.language)
	});

	$scope.changeLanguage = function(pLanguage) {
		$scope.language = pLanguage;
		$scope.registration.language = pLanguage;
		$rootScope.messages = $rootScope.messagesStore[pLanguage];
	}

	$scope.addCrew = function() {
		if (!$scope.registration.crews)
			$scope.registration.crews = new Array();
		var lCrew = new Object();
		lCrew.id = $scope.getRandomId();
		
		$scope.registration.crews.push(lCrew);
	}

	$scope.removeCrew = function(pId) {
		if ($scope.registration.crews && pId >= 0) {
			$scope.registration.crews.splice(pId, 1);
		}
	}

	$scope.addCoach = function() {
		if (!$scope.registration.coaches)
			$scope.registration.coaches = new Array();
		var lCoach = new Object();
		lCoach.id = $scope.getRandomId();

		$scope.registration.coaches.push(lCoach);
	}

	$scope.removeCoach = function(pId) {
		if ($scope.registration.coaches && pId >= 0) {
			$scope.registration.coaches.splice(pId, 1);
		}
	}

	$scope.changeCrewType = function(pCrew) {
		if (pCrew) {
			pCrew.members = new Array();
			for ( var i = 0; i < $scope.getMembersCount(pCrew.type); i++) {
				var lMember = new Object();
				lMember.sex = $scope.getMembersSex(pCrew.type);
				pCrew.members.push(lMember)
			}
			pCrew.captain = $scope.getMembersCount(pCrew.type) - 1;

		}
	}

	$scope.getMembersCount = function(pType) {

		switch (pType) {
		case "M4x":
		case "W4x":
			return 4;
		case "M8+":
		case "W8+":
			return 9;
		}

	};

	$scope.getMembersSex = function(pType) {
		switch (pType) {
		case "W8+":
		case "W4x":
			return "F";
		case "M8+":
		case "M4x":
			return "M";
		}

	};

	$scope.getTotalPrice = function() {
		var lAthletesCnt = 0;
		if ($scope.registration.crews){
			var lCount = $scope.getAthletesCount();
			lAthletesCnt = lCount.members;			
		}
		
		var lCoachesCnt = 0;
		if ($scope.registration.coaches)
			for ( var i = 0; i < $scope.registration.coaches.length; i++) {
				var lCoach = $scope.registration.coaches[i];
				if (lCoach.name && lCoach.surname)
					lCoachesCnt++;
			}

		var lMembersPrice = ($scope.getCurrentPrice() * (lCoachesCnt + lAthletesCnt));
		var lDinnerPrice = ($scope.registration.meals == 2) ? $scope.dinnerPrice : 0;
		
		$scope.registration.totalPrice = (lMembersPrice+lDinnerPrice) + " Euros";
				
		return (lMembersPrice+lDinnerPrice)
				+ " Euros";
	}

	$scope.getCurrentPrice = function() {
		if (new Date() - new Date(2014, 3, 14) < 0)
			return 30;
		else
			return 40;

	}

	$scope.getRandomId = function() {
		return Math.round(Math.random() * 10000);
	}

	$scope.getPlaceLabel = function(pCrew, pNum) {
		return $rootScope.messages.labels.places[$scope
				.getMembersCount(pCrew.type)][pNum];
	}
	
	$scope.validateCoaches = function(){
		var lResult = true;
		
		var lCoaches = $scope.registration.coaches;
		if(lCoaches){
			for(var i = 0; i < lCoaches.length; i++){
				if(!lCoaches[i].name || !lCoaches[i].surname || !lCoaches[i].sex)
					lResult = false;
			}
		}
		 
		
		return lResult;
	}

	$scope.validateCrews = function (){
		var lResult = true;

		var lCrews = $scope.registration.crews;
		
		if(lCrews && lCrews.length > 0){
		
			for(var i = 0; i < lCrews.length; i++){
				if(!lCrews[i].type){
					lResult = false;
					continue;
				}
				
				if(!lCrews[i].captain)
					lResult = false;
				
				
				for(var j = 0; j < lCrews[i].members.length; j++ ){
					var lMember = lCrews[i].members[j];
					
					if(!$scope.validateAthlete(lMember))
						lResult = false;
					
				}
			}
		}else{
			lResult = false;
		}
		 
		var lCounts = $scope.getAthletesCount();
		
		if(lCounts.places > lCounts.members)
			lResult = false;
		
		return lResult;
	}
	
	$scope.getAthletesCount = function(){
		var lCrews = $scope.registration.crews;
		var lMembers = new Object();
		var lPlacesCnt = 0;
		var lMembersCnt = 0;
		var lGirlsCnt = 0;
		var lGuysCnt = 0;
	
		if(lCrews && lCrews.length > 0){
			
			for(var i = 0; i < lCrews.length; i++){
				if(!lCrews[i].type){
					continue;
				}
				
				
				
				for(var j = 0; j < lCrews[i].members.length; j++ ){
					var lMember = lCrews[i].members[j];
					
					if($scope.validateAthlete(lMember)){
						var lMemberId = lMember.name+lMember.surname+lMember.licence+lMember.sex;
						
						lMembers[lMemberId] = lMember.sex;
						lPlacesCnt++;
					}
				}
			}
		}
		
		
		for (var lMember in lMembers) {
		    if (lMembers.hasOwnProperty(lMember)) {
		    	if(lMembers[lMember] == 'F'){
		    		lGirlsCnt++;
		    	}else{
		    		lGuysCnt++;
		    	}
		        lMembersCnt++;
		    }
		}
		
		return {
			places : lPlacesCnt,
			members : lMembersCnt, 
			girls : lGirlsCnt,
			guys : lGuysCnt
		}
	}
	
	$scope.validateAthlete = function(pAthlete){
		return (pAthlete.name && pAthlete.surname && pAthlete.licence && pAthlete.sex)
			
	}

	$scope.validateData = function (){
		var lResult = true;
		var lFormEmpty = true;
		var lData = $scope.registration;
		var lInvalidFields = new Array();
		if(!lData){
			lResult = false
			lFormEmpty = true;
		}
		
		$("[required=required]").each(function(){
			if($(this).val() == "")
				lInvalidFields.push($(this).attr("id"));
		})
		if(lInvalidFields.length > 0){
			lResult = false;
		}
		
		for(i = 0; i < lInvalidFields.length; i ++ ){
			$("#"+lInvalidFields[i]).attr("class", "invalid");
		}
		return lResult;
	}
	
	
	
	$scope.validateForm = function(pErrors){
		var lResult = true;
		
		lResult = ($scope.validateData() && $scope.validateCrews() && $scope.validateCoaches());
		
		if(!lResult)
			alert("Oh boy")
		
		return lResult;
		
	}
	
	$scope.submitForm = function(){
		$scope.notifications = new Array();

		var lResult = true;
		var lMessage = ""
		var lErrors = new Array();
		if (!$scope.validateForm()) {
			lResult = false;
			$scope.addErrorNotification($scope.messages.errors.invalidForm);
			$scope.errors = lErrors;
		} else {
			$scope.errors = new Array();
			$scope.validatingForm = true;

			$http({
				method : 'POST',
				url : "../server/service/saveForm",
				data : $scope.registration
			}).success(function(data, status) {
				if (data == "OK") {
					$scope.completeForm = true;
					$scope.inProgress = false;
					$('html,body').scrollTop(0);
				} else
					alert($scope.messages.errors.serverError);
				$scope.validatingForm = false;
			});
		}
		if (lMessage)
			alert(lMessage);
		return lResult;

		
	}

	$scope.addWarningNotification = function(pText){
		$scope.addNotification(pText, "warning");
	}
	$scope.addErrorNotification = function(pText){
		$scope.addNotification(pText, "error");
	}
	$scope.addNotification = function(pText, pType){
		var lNotification = new Object();
		lNotification.text = pText;
		lNotification.type = pType;
		$scope.notifications.push(lNotification);
	}

}

var registrationApp = angular.module('registrationApp', []);

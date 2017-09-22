var app = angular.module('controllers');
    app.controller('addVerb', function ($scope, $rootScope, txtContent, $location, $http, $interval, ngDialog, dropdownMenuBarInit, AuthService, Resources, $timeout) {
        // Comprobación del login   IMPORTANTE!!! PONER EN TODOS LOS CONTROLADORES
        if (!$rootScope.isLogged) {
            $location.path('/home');
            $rootScope.dropdownMenuBarValue = '/home'; //Dropdown bar button selected on this view
        }
        // Pedimos los textos para cargar la pagina
        txtContent("addVerb").then(function (results) {
            $scope.content = results.data;
            //$scope.initAddWordtest();

        });
        //Dropdown Menu Bar
        $rootScope.dropdownMenuBar = null;
        $rootScope.dropdownMenuBarButtonHide = false;
        $rootScope.dropdownMenuBarValue = '/panelGroups'; //Button selected on this view
        $rootScope.dropdownMenuBarChangeLanguage = false;//Languages button available

        //Choose the buttons to show on bar
        dropdownMenuBarInit($rootScope.interfaceLanguageId)
            .then(function () {
                //Choose the buttons to show on bar
                angular.forEach($rootScope.dropdownMenuBar, function (value) {
                    if (value.href == '/' || value.href == '/panelGroups' || value.href == '/userConfig' || value.href == '/faq' || value.href == '/tips' || value.href == '/privacy' || value.href == 'logout') {
                        value.show = true;
                    } else {
                        value.show = false;
                    }
                });
            });
        //function to change html view
        $scope.go = function (path) {
            if (path == 'logout') {
                $('#logoutModal').modal('toggle');
            } else {
                $rootScope.dropdownMenuBarValue = path; //Button selected on this view
                $location.path(path);
            }
        };
        //Log Out Modal
        $scope.img = [];
        $scope.img.lowSorpresaFlecha = '/img/srcWeb/Mus/lowSorpresaFlecha.png';
        Resources.main.get({'section': 'logoutModal', 'idLanguage': $rootScope.interfaceLanguageId}, {'funct': "content"}).$promise
            .then(function (results) {
                $scope.logoutContent = results.data;
            });
        $scope.logout = function () {
            $timeout(function () {
                AuthService.logout();
            }, 1000);
        };

        //Rebuild Scrollbar inside timeout
        $timeout(function () {
            $scope.$broadcast('rebuild:conjugationScrollbar');
        });
        /*
        $scope.$on('rebuild:conjugationScrollbar', function () {
            $scope.$broadcast('rebuild:conjugationScrollbar');
        });
        */
        $timeout(function () {
            $scope.$broadcast('rebuild:verbPatternScrollbar');
        });

        /*
        $scope.$on('rebuild:verbPatternScrollbar', function () {
            $scope.$broadcast('rebuild:verbPatternScrollbar');
        });
        */


        $scope.imgPicto = 'arrow question.png';
        $scope.verb ='';
        $scope.pronominal = false;
        //var persona = {ps1:'', ps2:'', ps3:'', pp1:'', pp2:'', pp3:''};
        function getContent() {
            var array = [];
            var obj = {};
            angular.forEach($scope.content, function (value, key) {
                    obj[key]=value;
                    array.push(obj);
            });
            return array;
        };

        //var content = $scope.content;
        function persona (){
            this.ps1 = '';
            this.ps2 = '';
            this.ps3 = '';
            this.pp1 = '';
            this.pp2 = '';
            this.pp3 = '';
        };
        function conjugation (name){
            this.name = name;
            this.persona = new persona();
        };

            $scope.presente = new conjugation('presente');
            $scope.perfecto = new conjugation('perfecto');
            $scope.imperfecto = new conjugation('imperfecto');
            $scope.pluscuamperfecto = new conjugation('pluscuamperfecto');
            $scope.pasado = new conjugation('pasado');
            $scope.futuro = new conjugation('futuro');
            $scope.prsubj = new conjugation('prsubj');
            $scope.impsubj = new conjugation('impsubj');
            $scope.imperativo = {name:'imperativo', persona:{ps2:'', ps3:'', pp1:'', pp2:'', pp3:''}};
            $scope.infinitivo = '';
            $scope.gerundio = '';
            $scope.participio = '';

        $scope.initAddWord = function () {
            switch ($scope.addWordType)
            {
                case "verb":
                    $scope.objAdd = {type: "verb", nomtext: null, mf: false, singpl: false, contabincontab: null, determinat: "1", ispropernoun: false, defaultverb: null, plural: null, femeni: null, fempl: null, imgPicto: 'arrow question.png', supExp: true};
                    $scope.switchName = {s1: false, s2: false, s3: false, s4: false, s5: false, s6: false};
                    $scope.NClassList = [];
                    $scope.errAdd = {erradd1: false, erradd2: false,erradd3: false};
                    $scope.classNoun = [{classType: "animate", numType: 1, nameType: $scope.content.classname1},
                        {classType: "human", numType: 2, nameType: $scope.content.classname2},
                        {classType: "pronoun", numType: 3, nameType: $scope.content.classname3},
                        {classType: "animal", numType: 4, nameType: $scope.content.classname4},
                        {classType: "planta", numType: 5, nameType: $scope.content.classname5},
                        {classType: "vehicle", numType: 6, nameType: $scope.content.classname6},
                        {classType: "event", numType: 7, nameType: $scope.content.classname7},
                        {classType: "inanimate", numType: 8, nameType: $scope.content.classname8},
                        {classType: "objecte", numType: 9, nameType: $scope.content.classname9},
                        {classType: "color", numType: 10, nameType: $scope.content.classname10},
                        {classType: "forma", numType: 11, nameType: $scope.content.classname11},
                        {classType: "joc", numType: 12, nameType: $scope.content.classname12},
                        {classType: "cos", numType: 13, nameType: $scope.content.classname13},
                        {classType: "abstracte", numType: 14, nameType: $scope.content.classname14},
                        {classType: "lloc", numType: 15, nameType: $scope.content.classname15},
                        {classType: "menjar", numType: 16, nameType: $scope.content.classname16},
                        {classType: "beguda", numType: 17, nameType: $scope.content.classname17},
                        {classType: "time", numType: 18, nameType: $scope.content.classname18},
                        {classType: "hora", numType: 19, nameType: $scope.content.classname19},
                        {classType: "month", numType: 20, nameType: $scope.content.classname20},
                        {classType: "week", numType: 21, nameType: $scope.content.classname21},
                        {classType: "tool", numType: 22, nameType: $scope.content.classname22},
                        {classType: "profession", numType: 23, nameType: $scope.content.classname23},
                        {classType: "material", numType: 24, nameType: $scope.content.classname24}];
                    break;
                default:
                    break;
            }
        };
        $scope.initAddWordtest = function () {

            if ($rootScope.addWordparam != null) {
                $scope.NewModif = $rootScope.addWordparam.newmod;
                $scope.addWordType = $rootScope.addWordparam.type;
                $rootScope.addWordparam = null;
                console.log($scope.addWordType);
            } else {
                $location.path("/panelGroups");
            }

            var URL = $scope.baseurl + "AddWord/getAllVerbs";
            $http.post(URL).
            success(function (response)
            {
                $scope.verbsList = response.data;
                if ($scope.NewModif == 0) {
                    $scope.idEditWord = $scope.addWordType;
                    var postdata = {id: $scope.idEditWord};
                    var URL = $scope.baseurl + "AddWord/EditWordType";
                    $http.post(URL, postdata).
                    success(function (response)
                    {
                        $scope.addWordType = response.data[0].type;
                        $scope.initAddWord();
                        $scope.editWordData();
                    });
                }
                else{
                    $scope.initAddWord();
                }

            });
        };
        $scope.editWordData = function () {
            var postdata = {id: $scope.idEditWord, type: $scope.addWordType};
            console.log(postdata);
            var URL = $scope.baseurl + "AddWord/EditWordGetData";
            $http.post(URL, postdata).
            success(function (response)
            {
                $scope.addWordEditData = response.data[0];
                console.log($scope.addWordEditData);

                var postdata = {id: $scope.idEditWord, type: $scope.addWordType};
                URL = $scope.baseurl + "AddWord/EditWordGetClass";
                $http.post(URL, postdata).
                success(function (response)
                {
                    switch ($scope.addWordType)
                    {
                        case "name":
                            console.log(response.data);
                            if (response.data){
                                for(i = 0; i < response.data.length;i++){
                                    $scope.addNClass(response.data[i].class);
                                }
                            }
                            $scope.objAdd = {type: "name", nomtext: $scope.addWordEditData.nomtext, mf: $scope.addWordEditData.mf == "masc" ? false : true,
                                singpl: $scope.addWordEditData.singpl == "sing" ? false : true, contabincontab: $scope.addWordEditData.contabincontab == "incontable" ? true : false,
                                determinat: $scope.addWordEditData.determinat, ispropernoun: $scope.addWordEditData.ispropernoun == 1 ? true : false,
                                defaultverb: $scope.addWordEditData.defaultverb == "0" ? null : $scope.addWordEditData.defaultverb, plural: $scope.addWordEditData.plural,
                                femeni: $scope.addWordEditData.femeni, fempl: $scope.addWordEditData.fempl, imgPicto: $scope.addWordEditData.imgPicto,
                                supExp: $scope.addWordEditData.supportsExpansion == "1" ? true : false};
                            $scope.switchName = {s1: false, s2: $scope.objAdd.femeni != null ? true : false, s3: $scope.objAdd.plural != null ? true : false,
                                s4: $scope.objAdd.fempl != null ? true : false, s5: $scope.objAdd.defaultverb != null ? true : false, s6: false};
                            break;
                        default:
                            break;
                    }
                });
            });
        };
        $scope.cancelAddWord = function () {
            $location.path("/panelGroups");
        };


        $scope.saveAddWord = function () {
            $scope.commit = 1;
            switch ($scope.addWordType)
            {
                case "name":
                    $scope.errAdd = {erradd1: false, erradd2: false,erradd3: false};
                    if($scope.objAdd.nomtext == null){
                        $scope.commit = 0;
                        $scope.errAdd.erradd1 = true;
                    }
                    if($scope.NClassList.length < 1 && $scope.objAdd.supExp){
                        $scope.commit = 0;
                        $scope.errAdd.erradd2 = true;
                    }
                    if($scope.objAdd.imgPicto == 'arrow question.png' || $scope.objAdd.imgPicto == null){
                        $scope.commit = 0;
                        $scope.errAdd.erradd3 = true;
                    }

                    if($scope.commit == 1)
                    {
                        $scope.objAdd = {
                            type: "name",
                            nomtext: $scope.objAdd.nomtext,
                            mf: $scope.objAdd.mf == false ? "masc" : "fem",
                            singpl: $scope.objAdd.singpl == false ? "sing" : "pl",
                            contabincontab: $scope.objAdd.contabincontab == true ? "incontable" : "contable",
                            determinat: $scope.objAdd.determinat,
                            ispropernoun: $scope.objAdd.ispropernoun == true ? "1" : "0",
                            defaultverb: $scope.objAdd.defaultverb == null ? "0" : $scope.objAdd.defaultverb,
                            plural: $scope.switchName.s3 == false ? $scope.objAdd.nomtext : $scope.objAdd.plural,
                            femeni: $scope.switchName.s2 == false ? null : $scope.objAdd.femeni,
                            fempl: $scope.switchName.s4 == false ? null : $scope.objAdd.fempl,
                            imgPicto: $scope.objAdd.imgPicto,
                            pictoid: $scope.idEditWord != null ? $scope.idEditWord : false,
                            new: $scope.NewModif == 1 ? true : false,
                            class: $scope.NClassList,
                            supExp: $scope.objAdd.supExp == true ? "1" : "0"};

                        if ($scope.objAdd.singpl == "pl"){
                            $scope.objAdd.plural = $scope.objAdd.nomtext;
                            $scope.objAdd.femeni = null;
                            $scope.objAdd.fempl = null;
                        }
                        if ($scope.objAdd.mf == "fem"){
                            $scope.objAdd.femeni = null;
                            $scope.objAdd.fempl = null;
                        }
                        if ($scope.objAdd.plural == null) {
                            $scope.objAdd.plural = $scope.objAdd.nomtext;
                        }
                        var URL = $scope.baseurl + "AddWord/InsertWordData";
                        var postdata = {objAdd: $scope.objAdd};
                        $http.post(URL, postdata).success(function (response)
                        {

                        });
                        $location.path("/panelGroups");
                    }
                    break;
                case "adj":
                    $scope.errAdd = {erradd1: false, erradd2: false,erradd3: false};
                    if($scope.objAdd.masc == null || $scope.objAdd.fem == null || $scope.objAdd.mascpl == null || $scope.objAdd.fempl == null){
                        $scope.commit = 0;
                        $scope.errAdd.erradd1 = true;
                    }
                    if($scope.AdjClassList.length < 1 && $scope.objAdd.supExp){
                        $scope.commit = 0;
                        $scope.errAdd.erradd2 = true;
                    }
                    if($scope.objAdd.imgPicto == 'arrow question.png' || $scope.objAdd.imgPicto == null){
                        $scope.commit = 0;
                        $scope.errAdd.erradd3 = true;
                    }

                    if($scope.commit == 1)
                    {
                        $scope.objAdd = {type: "adj", masc: $scope.objAdd.masc, fem: $scope.objAdd.fem, mascpl: $scope.objAdd.mascpl,
                            fempl: $scope.objAdd.fempl, defaultverb: $scope.switchAdj.s1 == false ? "86" : "100", subjdef: $scope.switchAdj.s2 == false ? "1" : "3",
                            imgPicto: $scope.objAdd.imgPicto, pictoid: $scope.idEditWord != null ? $scope.idEditWord : false, new: $scope.NewModif == 1 ? true : false,
                            class: $scope.AdjClassList, supExp: $scope.objAdd.supExp == true ? "1" : "0"};
                        var URL = $scope.baseurl + "AddWord/InsertWordData";
                        var postdata = {objAdd: $scope.objAdd};
                        $http.post(URL, postdata).success(function (response)
                        {

                        });
                        $location.path("/panelGroups");
                    }
                    break;
                default:
                    break;
            }



        };
        $scope.uploadFileToWord = function () {
            $scope.myFile = document.getElementById('file-input').files;
            $scope.uploading = true;
            var i;
            var uploadUrl = $scope.baseurl + "ImgUploader/upload";
            var fd = new FormData();
            fd.append('vocabulary', angular.toJson(true));
            for (i = 0; i < $scope.myFile.length; i++) {
                fd.append('file' + i, $scope.myFile[i]);
            }
            $http.post(uploadUrl, fd,{
                headers: {'Content-Type': undefined}
            })
                .success(function (response) {
                    $scope.uploading = false;
                    $scope.objAdd.imgPicto = response.url;
                    $scope.objAdd.imgPicto = $scope.objAdd.imgPicto.split('/');
                    $scope.objAdd.imgPicto = $scope.objAdd.imgPicto[2];
                    if (response.error) {
                        console.log(response.errorText);
                        $scope.errorText = response.errorText;
                        $('#errorImgModal').modal({backdrop: 'static'});
                    }
                })
                .error(function (response) {
                });
        };

        $scope.img = [];
        $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
        $scope.style_changes_title = '';

        // Activate information modals (popups)
        $scope.toggleInfoModal = function (title, text) {
            $scope.infoModalContent = text;
            $scope.infoModalTitle = title;
            $scope.style_changes_title = 'padding-top: 2vh;';
            $('#infoModal').modal('toggle');
        };

        $scope.getConjugations = function (){
            var URL = $scope.baseurl + "addVerb/getConjugations";
            var postdata = {verb : $scope.verb};
            $http.post(URL, postdata).success(function (response) {
                $scope.conjugations = response;
            });
            setConjugations();
        };
        function setConjugations(){
            setPresente();
            setPerfecto();
            setImperfecto();
            setPluscuamperfecto();
            setPasado();
            setFuturo();
            setPrsubj();
            setImpsubj();
            setImperativo();
            setFormasNoPersonales();
        }
        function setPresente(){
            $scope.presente.persona.ps1 = $scope.conjugations.presente.ps1;
            $scope.presente.persona.ps2 = $scope.conjugations.presente.ps2;
            $scope.presente.persona.ps3 = $scope.conjugations.presente.ps3;
            $scope.presente.persona.pp1 = $scope.conjugations.presente.pp1;
            $scope.presente.persona.pp2 = $scope.conjugations.presente.pp2;
            $scope.presente.persona.pp3 = $scope.conjugations.presente.pp3;
        }
        function setPerfecto(){
            $scope.perfecto.persona.ps1 = $scope.conjugations.perfecto.ps1;
            $scope.perfecto.persona.ps2 = $scope.conjugations.perfecto.ps2;
            $scope.perfecto.persona.ps3 = $scope.conjugations.perfecto.ps3;
            $scope.perfecto.persona.pp1 = $scope.conjugations.perfecto.pp1;
            $scope.perfecto.persona.pp2 = $scope.conjugations.perfecto.pp2;
            $scope.perfecto.persona.pp3 = $scope.conjugations.perfecto.pp3;
        }
        function setImperfecto(){
            $scope.imperfecto.persona.ps1 = $scope.conjugations.imperfecto.ps1;
            $scope.imperfecto.persona.ps2 = $scope.conjugations.imperfecto.ps2;
            $scope.imperfecto.persona.ps3 = $scope.conjugations.imperfecto.ps3;
            $scope.imperfecto.persona.pp1 = $scope.conjugations.imperfecto.pp1;
            $scope.imperfecto.persona.pp2 = $scope.conjugations.imperfecto.pp2;
            $scope.imperfecto.persona.pp3 = $scope.conjugations.imperfecto.pp3;
        }
        function setPluscuamperfecto(){
            $scope.pluscuamperfecto.persona.ps1 = $scope.conjugations.perfecto.ps1;
            $scope.pluscuamperfecto.persona.ps2 = $scope.conjugations.perfecto.ps2;
            $scope.pluscuamperfecto.persona.ps3 = $scope.conjugations.perfecto.ps3;
            $scope.pluscuamperfecto.persona.pp1 = $scope.conjugations.perfecto.pp1;
            $scope.pluscuamperfecto.persona.pp2 = $scope.conjugations.perfecto.pp2;
            $scope.pluscuamperfecto.persona.pp3 = $scope.conjugations.perfecto.pp3;
        }
        function setPasado(){
            $scope.pasado.persona.ps1 = $scope.conjugations.pasado.ps1;
            $scope.pasado.persona.ps2 = $scope.conjugations.pasado.ps2;
            $scope.pasado.persona.ps3 = $scope.conjugations.pasado.ps3;
            $scope.pasado.persona.pp1 = $scope.conjugations.pasado.pp1;
            $scope.pasado.persona.pp2 = $scope.conjugations.pasado.pp2;
            $scope.pasado.persona.pp3 = $scope.conjugations.pasado.pp3;
        }
        function setFuturo(){
            $scope.futuro.persona.ps1 = $scope.conjugations.futuro.ps1;
            $scope.futuro.persona.ps2 = $scope.conjugations.futuro.ps2;
            $scope.futuro.persona.ps3 = $scope.conjugations.futuro.ps3;
            $scope.futuro.persona.pp1 = $scope.conjugations.futuro.pp1;
            $scope.futuro.persona.pp2 = $scope.conjugations.futuro.pp2;
            $scope.futuro.persona.pp3 = $scope.conjugations.futuro.pp3;
        }
        function setPrsubj(){
            $scope.prsubj.persona.ps1 = $scope.conjugations.prsubj.ps1;
            $scope.prsubj.persona.ps2 = $scope.conjugations.prsubj.ps2;
            $scope.prsubj.persona.ps3 = $scope.conjugations.prsubj.ps3;
            $scope.prsubj.persona.pp1 = $scope.conjugations.prsubj.pp1;
            $scope.prsubj.persona.pp2 = $scope.conjugations.prsubj.pp2;
            $scope.prsubj.persona.pp3 = $scope.conjugations.prsubj.pp3;
        }
        function setImpsubj(){
            $scope.impsubj.persona.ps1 = $scope.conjugations.impsubj.ps1;
            $scope.impsubj.persona.ps2 = $scope.conjugations.impsubj.ps2;
            $scope.impsubj.persona.ps3 = $scope.conjugations.impsubj.ps3;
            $scope.impsubj.persona.pp1 = $scope.conjugations.impsubj.pp1;
            $scope.impsubj.persona.pp2 = $scope.conjugations.impsubj.pp2;
            $scope.impsubj.persona.pp3 = $scope.conjugations.impsubj.pp3;
        }
        function setImperativo(){
            $scope.imperativo.persona.ps2 = $scope.conjugations.imperativo.ps2;
            $scope.imperativo.persona.ps3 = $scope.conjugations.imperativo.ps3;
            $scope.imperativo.persona.pp1 = $scope.conjugations.imperativo.pp1;
            $scope.imperativo.persona.pp2 = $scope.conjugations.imperativo.pp2;
            $scope.imperativo.persona.pp3 = $scope.conjugations.imperativo.pp3;
        }
        function setFormasNoPersonales(){
            $scope.infinitivo = $scope.conjugations.infinitivo;
            $scope.gerundio = $scope.conjugations.gerundio;
            $scope.participio = $scope.conjugations.participio;
        }

        //PATRONES VERBALES

        $scope.verbPattern1 = false;
        $scope.verbPattern2 = false;

        $scope.showPattern1 = {'CD':false,'Receiver':false,'Beneficiary':false,'Acomp':false,'Tool':false,'Modo':false,'Locto':false};
        $scope.showPattern2 = {'CD':false,'Receiver':false,'Beneficiary':false,'Acomp':false,'Tool':false,'Modo':false,'Locto':false};

        $scope.Pattern1 = {
            'Patron': {'pronominal': false, 'subj':"", 'subjdef':"", 'defaulttense':"", 'exemple':""},
            'CD': {'priority': 0, 'type':"", 'preposition':""},
            'Receiver': {'priority':0, 'preposition':""},
            'Beneficiary': {'priority':0, 'type':"", 'preposition':""},
            'Acomp': {'priority':0, 'preposition':""},
            'Tool': {'priority':0, 'preposition':""},
            'Modo': {'priority':0, 'type':""},
            'Locto': {'priority':0, 'type':"", 'preposition':""},
        };

        $scope.subjOptions = function(){
            if ($scope.interfaceLanguageId == 1){//CAT
                return [{value:"noun", name:"Substantiu", common:true},{value:"animate" ,name:"Animat", common:true},{value:"human", name:"Huma", common:true},
                        {value:"pronoun", name:"Pronom", common:false},{value:"animal", name:"Animal", common:false},{value:"planta", name:"Planta", common:false},
                        {value:"vehicle", name:"Vehicle", common:false},{value:"event", name:"Event", common:false},{value:"inanimate", name:"Inanimat", common:false},
                        {value:"objecte", name:"Objecte", common:true},{value:"color", name:"Color", common:false},{value:"forma", name:"Forma", common:false},
                        {value:"joc", name:"Joc", common:false},{value:"cos", name:"Cos", common:false},{value:"abstracte", name:"Abstracte", common:false},
                        {value:"lloc", name:"Lloc", common:false},{value:"menjar", name:"Menjar", common:false},{value:"beguda", name:"Beguda", common:false},
                        {value:"hora", name:"Hora", common:false},{value:"month", name:"Mes", common:false},
                        {value: "week", name:"Setmana", common:false},{value:"tool", name:"Eina", common:false},{value:"profession", name:"Professió", common:false},
                        {value:"material", name:"Material", common:false}, {value:"verb", name:"Verb", common:true}];
            }else if($scope.interfaceLanguageId == 2){//ESP
                return [{value:"noun", name:"Sustantivo", common:true},{value:"animate" ,name:"Animado", common:true},{value:"human", name:"Humano", common:true},
                        {value:"pronoun", name:"Pronombre", common:false},{value:"animal", name:"Animal", common:false},{value:"planta", name:"Planta", common:false},
                        {value:"vehicle", name:"Vehiculo", common:false},{value:"event", name:"Evento", common:false},{value:"inanimate", name:"Inanimado", common:false},
                        {value:"objecte", name:"Objeto", common:true},{value:"color", name:"Color", common:false},{value:"forma", name:"Forma", common:false},
                        {value:"joc", name:"Juego", common:false},{value:"cos", name:"Cosa", common:false},{value:"abstracte", name:"Abstracto", common:false},
                        {value:"lloc", name:"Lugar", common:false},{value:"menjar", name:"Comida", common:false},{value:"beguda", name:"Bebida", common:false},
                        {value:"hora", name:"Hora", common:false},{value:"month", name:"Mes", common:false},
                        {value: "week", name:"Semana", common:false},{value:"tool", name:"Herramienta", common:false},{value:"profession", name:"Profesión", common:false},
                        {value:"material", name:"Material", common:false}, {value:"verb", name:"Verbo", common:true}];
            }
        }();

        $scope.defecto = function (){
            if ($scope.interfaceLanguageId == 1){
                return [{value:1, name:"jo"},{value:2, name:"tu"},{value:"una cosa", name:"objecte"}]
            }else if($scope.interfaceLanguageId == 2){
                return [{value:1, name:"yo"},{value:2, name:"tu"},{value:"una cosa", name:"objeto"}]
            }
        }();

        $scope.patternTemp = function (){
            if ($scope.interfaceLanguageId == 1){
                return [{value:"present", name:"Present"},{value:"perfet", name:"Passat immediat"},{value:"perifrastic", name:"Passat"},{value:"futur", name:"Futur"},{value:"imperatiu", name:"Imperatiu"}]
            }else if($scope.interfaceLanguageId == 2){
                return [{value:"present", name:"Presente"},{value:"perfet", name:"Pasado inmediato"},{value:"perifrastic", name:"Pasado"},{value:"futur", name:"Futuro"},{value:"imperatiu", name:"Imperativo"}]
            }
        }();

        $scope.complementos = function (){
            if ($scope.interfaceLanguageId == 1){
                return [{id:1, name:"Complement directe"},{id:2, name:"Receiver"},{id:3, name:"Beneficiary"},
                    {id:4, name:"Acompanyant"}, {id: 5, name:"Tool"}, {id:6, name:"Complement de manera"}, {id:7, name:"Complement de lloc"}]
            }else if($scope.interfaceLanguageId == 2){
                return [{id:1, name:"Complemento directo"},{id:2, name:"Receiver"},{id:3, name:"Beneficiary"},
                        {id:4, name:"Acompañante"}, {id: 5, name:"Tool"}, {id:6, name:"Complemento de modo"}, {id:7, name:"Complemento de lugar"}]
            }
        }();

        $scope.themeOptions = function (){
            if ($scope.interfaceLanguageId == 1){
                return[{value:"opt", name:"Opcional"}, {value:"1", name:"Obligatori"}];
            }else if ($scope.interfaceLanguageId == 2){
                return[{value:"opt", name:"Opcional"}, {value:"1", name:"Obligatorio"}];
            }
        }();

        $scope.maneraOptions = function (){
            if ($scope.interfaceLanguageId == 1){
                return [{value:"adj", name:"Adjectiu"},{value:"adv", name:"Adverbi"},{value:"quant", name:"Quantificador"}]
            }else if($scope.interfaceLanguageId == 2){
                return [{value:"adj", name:"Adjetivo"},{value:"adv", name:"Adverbio"},{value:"quant", name:"Cuantificador"}]
            }
        }();

        $scope.LocatBenefOptions = function (){
            var array = $scope.subjOptions;
            if ($scope.interfaceLanguageId == 1){
                var b = [{value:"adv", name:"Adverbi", common:true}]
            }else if ($scope.interfaceLanguageId == 2){
                var b = [{value:"adv", name:"Adverbio", common:true}]
            }
            return array.concat(b);
        }();

        $scope.showComplement = function(id){
          switch(id) {
              case 1:
                  $scope.showPattern1.CD = true
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
              case 2:
                  $scope.showPattern1.Receiver = true;
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
              case 3:
                  $scope.showPattern1.Beneficiary = true;
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
              case 4:
                  $scope.showPattern1.Acomp = true;
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
              case 5:
                  $scope.showPattern1.Tool = true;
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
              case 6:
                  $scope.showPattern1.Modo = true;
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
              case 7:
                  $scope.showPattern1.Locto = true;
                  $scope.$broadcast('rebuild:verbPatternScrollbar');
                  break;
          }
        };

        $scope.cleanPattern = function (showPattern, Pattern, property){
            switch(property) {
                case 'CD':
                    showPattern.CD = false;
                    Pattern.CD = {'priority': 0, 'type':"", 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
                case 'Receiver':
                    showPattern.Receiver = false;
                    Pattern.Receiver = {'priority':0, 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
                case 'Beneficiary':
                    showPattern.Beneficiary = false;
                    Pattern.Beneficiary = {'priority':0, 'type':"", 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
                case 'Acomp':
                    showPattern.Acomp = false;
                    Pattern.Acomp = {'priority':0, 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
                case 'Tool':
                    showPattern.Tool = false;
                    Pattern.Tool = {'priority':0, 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
                case 'Modo':
                    showPattern.Modo = false;
                    Pattern.Modo = {'priority':0, 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
                case 'Locto':
                    showPattern.Locto = false;
                    Pattern.Locto = {'priority': 0, 'type':"", 'preposition':""};
                    $scope.$broadcast('rebuild:verbPatternScrollbar');
                    break;
            }
        }

            $scope.guardar = function(){
            console.log("GUARDANDO...");
            console.log($scope.Pattern1);
        };
    });


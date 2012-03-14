$(function() { 

    //bootsrap plugin for login 
    $('.dropdown-toggle').dropdown();
    
    //Stop Dropdown closing when i click on it.
    $('.dropdown-menu').find('form').click(function (e) {
            e.stopPropagation();
    });

if ( window.location.pathname == '/coupons/users/register' ) {
    function optionTemplate( count ) {

        return (count>9)?"<option value=\""+count+"\">"+count+"</option>":"<option value=\"0"+count+"\">0"+count+"</option>";
    }

    function hourOptions( hourFormat ) {
    
        var optionsBlock;

        if( hourFormat == 24 ) {
            
            for( var i = 0; i < 24; i++ ) {
                //separate two-digit from one-digit numbers
                optionsBlock += optionTemplate( i );
            }

            return optionsBlock;
        }

        //default 12 
        for( var i = 0; i < 12; i++ ) {

            optionsBlock += optionTemplate( i );
        }

        return optionsBlock;
    }

    function minuteOptions( minuteFormat ) {

        var optionsBlock;
        
        for( var i = 0; i < 60; i = i+minuteFormat ) {

            optionsBlock += optionTemplate( i );

        }

        return optionsBlock;
    }

    function dayOptions() {

        var array = ['Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευη', 'Σάββατο', 'Κυριακή'];
        var optionsBlock;

        for( var i = 1; i<=7; i++ ) {

            optionsBlock += "<option value=\""+i+"\">"+array[i-1]+"</option>";
        }

        return optionsBlock;
    }

    function meridianOptions( type ) {

        var gr = [ 'π.μ', 'μ.μ' ];
        var en = [ 'a.m', 'p.m' ];
        var optionsBlock;

        if( type == 'gr' ) {

            for( var i = 0; i < 1; i++ ) {
                optionsBlock += "<option value\""+en[i]+"\">"+gr[i]+"</otpion>";
            }
            return optionsBlock;
        }
        //default is en
        for( var i = 0; i < 1; i++ ) {
            optionsBlock += "<option value\""+en[i]+"\">"+en[i]+"</otpion>";
        }

        return optionsBlock;
    }



    function divTemplate( map ) {
        
        var labelElement = $(document.createElement( 'label')).attr( 'for', map.id);
        labelElement.html( map.label );
//        var labelElement = "<label for=\""+map.id+"\">"+map.label+"</label>";
//
        var divElement = $(document.createElement('div')).attr( 'class', map.divClass);
        divElement.append( labelElement );
        divElement.append( map.options );
        //var divElement =  "<div class=\""+map.divClass+"\">"+labelElement+map.options+"</div>";
        return divElement;
    }

    function dayDiv(map) {
        
        var select = $(document.createElement('select')).attr( 'name', map.selectName ).attr('id', map.id ).attr('class', map.selectClass );
        select.html( dayOptions() );

        var div = divTemplate( {
                    label: map.label,
                    divClass:map.divClass,
                    id:map.id,
                    options:select
        });
      return div;
    }

    function timeDiv( map ) {
        //for hour to minute
        var separator = $(document.createElement('span'));
        separator.html( ":" );

        //standard meridian separator
        var mersep = $(document.createElement('span'));
        mersep.html( " " );

        var selectHour = 
            $(document.createElement('select')).attr( "name", map.hourName ).attr( "id", map.hourId ).attr( "class", map.hourClass );
        selectHour.append( hourOptions( map.hourType ) );

        var selectMinute = 
            $(document.createElement('select')).attr( "name", map.minuteName ).attr( "id", map.minuteId ).attr( "class", map.minuteClass );
        selectMinute.append(minuteOptions(map.interval));

        //default represantation of time
        var time = selectHour.after( separator ).after( selectMinute ); 

        var selectMeridian = null;

        if( map.hourType == 12 ) {

            selectMeridian = 
                $(document.createElement('select')).attr( "name", map.meridianName )
                .attr( "id", map.meridianId ).attr( "class", map.meridianClass );

            selectMeridian.append(meridianOptions(map.meridianLang));           
        }

       var div = divTemplate( {
                    label: map.label,
                    divClass:map.divClass,
                    id:map.hourId,
                    options:(selectMeridian==null)?time:selectHour.after( mersep ).after( selectMeridian )
        });

        return div;

    }

    function buttonDiv( map ) {
    
        var button = $(document.createElement('a')).attr( "class", "btn" ).attr("id", map.buttonId );
        button.html( map.content );

        var div = divTemplate( { 
            label: map.label,
            divClass: map.divClass,
            id:map.buttonId,
            options:button
        });

        return div;
    }

    function createColumn( content ) {

        var col = $(document.createElement('td'));
        col.html( content );
        return col;
    }
    
    function createRow( counter, startingTime, endingTime, dayMap, removeButton, createButton) {
        
        var day = createColumn( dayDiv( dayMap ) );
        var starting = createColumn( timeDiv( startingTime ) );
        var ending = createColumn( timeDiv( endingTime ) );
        var rbutton = createColumn( buttonDiv( removeButton ) );

        var row = $(document.createElement('tr')).attr( "id" ,"row"+counter );
        row.append( day );
        row.append( starting );
        row.append( ending );
        row.append( rbutton );

        if( counter == 0 ) {

            var table = $(document.createElement('table')).attr("class", "table table-bordered").attr( "id", "table" );
            
            table.html( '<thead><tr><th>Ημέρα</th><th>Ώρα έναρξης</th><th>Ώρα λήξης</th><th>Επιλογές</th></tr></thead>');
            
            table.append( row );

            table.appendTo( "#table" );

        } else {
        
            row.appendTo( "tbody" );
        }

        
    }

    var counter = 0;
    var removed = 0;

   //end of conf maps

    $("#create").live("click", function() {
         var dayMap ={
                    label: "",
                    divClass:"input select required",
                    id:"WorkHour" + counter + "DayId",
                    selectName:"data[WorkHour]["+ counter +"][day_id]",
                    selectClass:""
          };

            var startingTime ={
                    label: "",
                    divClass:"input time",
                    hourName:"data[WorkHour]["+ counter +"][starting][hour]",
                    minuteName:"data[WorkHour]["+ counter +"][starting][min]",
                    hourId:"WorkHour" + counter + "StartingHour",
                    minuteId:"WorkHour" + counter + "StartingMin",
                    hourClass:"span3",
                    minuteClass:"span3",
                    hourType:24,
                    interval:15,
                   // meridianName:"data[WorkHour]["+ counter +"][starting][meridian]",
                   // meridianId:"WorkHour" + counter + "StartingMeridian",
                   // meridianLang: "gr" //or en
                   // meridianClass:"span2",

          };

            var endingTime ={
                    label: "",
                    divClass:"input time",
                    hourName:"data[WorkHour]["+ counter +"][ending][hour]",
                    minuteName:"data[WorkHour]["+ counter +"][ending][min]",
                    hourId:"WorkHour" + counter + "EndingHour",
                    minuteId:"WorkHour" + counter + "EndingMin",
                    hourClass:"span3",
                    minuteClass:"span3",
                    hourType:24,
                    interval:15,
                   // meridianName:"data[WorkHour]["+ counter +"][starting][meridian]",
                   // meridianId:"WorkHour" + counter + "StartingMeridian",
                   // meridianLang: "gr"//or en
                   // meridianClass:"span2",

          };

            var removeButton = {
                label:"",
                divClass:counter,
                buttonId:"remove",
                content:"Αφαίρεση"

            };

            var createButton = {

                label:"",
                divClass:"createButton",
                buttonId:"create",
                content:"Προσθήκη"
            }

        createRow( counter,startingTime, endingTime, dayMap, removeButton, createButton );

        counter++;
    });


    $("#remove").live( "click", function() {

        removed++;


        var id = $(this).parent().attr('class');
        $("#row"+id).remove();

        if( counter == removed ) {

            removed = 0;
            counter = 0;
            $('table').remove();
        }
    });

}


});

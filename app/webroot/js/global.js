$(function() { 

    //bootsrap plugin for login 
    $('.dropdown-toggle').dropdown();
    
    //Stop Dropdown closing when i click on it.
    $('.dropdown-menu').find('form').click(function (e) {
            e.stopPropagation();
    });


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

        var labelElement = "<label for=\""+map.id+"\">"+map.label+"</label>";
        var divElement =  "<div class=\""+map.divClass+"\">"+labelElement+map.options+"</div>";
        return divElement;
    }

    function dayDiv(map) {

        var select = "<select name=\""+map.selectName+"\""
            +" id=\""+map.id+"\""
            +" class=\""+map.selectClass+"\">"
            +dayOptions()+"</select>";

        var div = divTemplate( {
                    label: map.label,
                    divClass:map.divClass,
                    id:map.id,
                    options:select
        });
      return div;
    }

    function timeDiv( map ) {

        var selectHour =  "<select name=\""+map.hourName+"\""
            +" id=\""+map.hourId+"\""
            +" class=\""+map.hourClass+"\">"
            +hourOptions(map.hourType)+"</select>";

        var selectMinute =  "<select name=\""+map.minuteName+"\""
            +" id=\""+map.minuteId+"\""
            +" class=\""+map.minuteClass+"\">"
            +minuteOptions(map.interval)+"</select>";

        var selectMeridian = null;

        if( map.hourType == 12 ) {
            
            var selectMeridian =  "<select name=\""+map.meridianName+"\""
                +" id=\""+map.meridianId+"\""
                +" class=\""+map.meridianClass+"\">"
                +meridianOptions( map.meridianLang )+"</select>";

        }

       var div = divTemplate( {
                    label: map.label,
                    divClass:map.divClass,
                    id:map.hourId,
                    options:(selectMeridian==null)?selectHour+":"+selectMinute:selectHour+":"+selectMinute+" "+selectMeridian
        });

        return div;

    }
    
    //TODO oloklhrwsh
    function createRow( counter, startingTime, endingTime, dayMap ) {
        
        var bodyRow = "<td>"+ dayDiv( dayMap )+"</td>"+"<td>"+timeDiv( startingTime )+"</td>"
                +"<td>"+timeDiv( endingTime )+"</td>";
        var removeButton = "<a class=\"btn\" id =\"remove\">αφαίρεση</a>";
        var divR = $(document.createElement('div')).attr( "class", "removeDiv").attr( "id", counter);
        var column = $(document.createElement('td'));
        divR.html( removeButton );
        column.append( divR );

        var row = $(document.createElement('tr')).attr( "id" ,"row"+counter );
        row.html( bodyRow );
        row.append( column );

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

    $("#create").click( function() {

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

        createRow( counter,startingTime, endingTime, dayMap );

        counter++;
    });


    $("#remove").live( "click", function() {

        counter--;//me epifilaxh
        var id = $(this).parent().attr('id');
        $("#row"+id).remove();

        if( counter == 0 ) {

            $('table').remove();
        }
    });


});

$(document).ready(
        function() {
            $('#date_start').CUI('datetimepicker', {
                duration: '',
                showTime: true,
                showHour: true,
				showMinute: true,
                constrainInput: false,
                stepMinutes: 1,
                stepHours: 1,
                altTimeField: '',
                time24h: false,
                dateFormat: 'dd/mm/y'
             });
            $('#date_end').CUI('datetimepicker', {
                duration: '',
                showTime: true,
                showHour: true,
				showMinute: true,
                constrainInput: false,
                stepMinutes: 1,
                stepHours: 1,
                altTimeField: '',
                time24h: false,
                dateFormat: 'dd/mm/y'
             });
        });
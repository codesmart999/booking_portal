function getDisplayTextFromMinutes(from_in_mins, to_in_mins) {
    var start_hour = Math.floor(from_in_mins / 60);
    var start_minutes = ('0' + (from_in_mins % 60)).slice(-2);
    var start_AP = (start_hour < 12) ? 'am' : 'pm';
    start_hour -= (start_hour <= 12) ? 0 : 12;
    start_hour = ('0' + start_hour).slice(-2);

    var end_hour = Math.floor(to_in_mins / 60);
    var end_minutes = ('0' + (to_in_mins % 60)).slice(-2);
    var end_AP = (end_hour < 12) ? 'am' : 'pm';
    end_hour -= (end_hour <= 12) ? 0 : 12;
    end_hour = ('0' + end_hour).slice(-2);

    return start_hour + ':' + start_minutes + '' + start_AP + ' To ' + end_hour + ':' + end_minutes + '' + end_AP;
}

$(document).ready(function() {
    $('#btnPrint').on('click', function() {
        // Open a new window or popup
        var printWindow = window.open('', '_blank', 'width=600,height=600');

        var printStyles = `
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .print-container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
        }
        .print-container h2 {
            color: #333;
        }
        .print-container p {
            margin: 10px 0;
        }
        .print-details {
            margin-top: 20px;
        }
        .print-details table {
            width: 100%;
            border-collapse: collapse; /* Added border-collapse property */
        }
        .print-details th,
        .print-details td {
            padding: 8px;
            border: 1px solid #333; /* Border added */
        }
        .print-details th {
            background-color: #f2f2f2;
            text-align: right; /* Aligning the text in th to right */
        }
        `;
        
        var styleElement = $('<style>').attr('type', 'text/css').text(printStyles);
        
        // Append the link element to the head of the new window or popup
        $(printWindow.document.head).append(styleElement);
        
        // Write the content of the current page to the new window or popup
        $(printWindow.document.body).html($('.print-container').html());
        
        // Print the content in the new window or popup
        printWindow.print();
        
        // Close the new window or popup after printing is complete
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    });
});
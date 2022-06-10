(function($){
        let recordsQuantity = 50
        let historybar =$('.transactions-pagebar')
        let historyContent = $('#transaction-history-content')
        let tableHeaders ="<th>No</th>"
        let tableRows = ""
        let pageRecord =0
        let beginning = 0
        for (let i = 0; i < columnsToPrint.columns.length; i++){
            tableHeaders+= "<th>"+columnsToPrint.columns[i]+"</th>"
        }
        const recordsTable = () =>{
            recordsQuantity = $("#amountOfRecords").val()*1
            beginning = pageRecord*recordsQuantity
            let endOfRecord = (beginning + recordsQuantity)>allrecords.records.length?allrecords.records.length:(beginning + recordsQuantity)
            tableRows = ""
            let transactions = allrecords.records    
            for (beginning; beginning<endOfRecord; beginning++){
                tableRows += `<tr><td>${beginning+1}</td>`
                let sigleRecord = transactions[beginning]
                columnsToPrint.columns.forEach(function(column){
                        tableRows += "<td>" + sigleRecord[column] + "</td>"
                    })
                tableRows += "</tr>"
             }
            let output = 
            `<table>
                <tr>
                    ${tableHeaders}
                </tr>
                    ${tableRows}
            </table>`
            return output
        }

        const navigator = (page = 0) =>{
            let pagesContainer = ""          
            let totalPages = Math.ceil(allrecords.records.length / $("#amountOfRecords").val());
            for (let i = 1; i<=totalPages;i++){
                if((page+1) == i){
                    pagesContainer += `<li><button class="active">${i}</button></li>`
                } else {
                    pagesContainer += `<li><button>${i}</button></li>`
                }
            }
            let navigationBar = `<li><button id = "previous">Anterior</button></li>
            ${pagesContainer}
            <li><button id = "next">Siguiente</button></li>`
            return navigationBar

        }

    $(document).ready(function(){

            historybar.html(navigator())
            historyContent.html(recordsTable())  
            $('.transactions-pagebar').on("click","li",function(){

                    let pressedButton = $(this).children().text()
                    switch(pressedButton){
                        case "Anterior":
                            pageRecord = pageRecord > 0 ? pageRecord - 1: 0
                        break

                        case "Siguiente":
                            pageRecord = pageRecord < (Math.ceil(allrecords.records.length / recordsQuantity)-1) ? pageRecord + 1 : pageRecord
                        break

                        default:
                        pageRecord = (pressedButton*1)-1                         
                    }
                    historybar.html(navigator(pageRecord))
                    historyContent.html(recordsTable())
            })
            $('#setRecordsPerPage').on("click",function(){
                pageRecord = 0
                historybar.html(navigator())
                historyContent.html(recordsTable())  
        })
            
    })

})(jQuery)

document.addEventListener("DOMContentLoaded", function() {
    // Fetch data for line chart
    fetchChartData();

    function fetchChartData() {
        // Use AJAX or fetch API to get data from the server
        // Replace 'your_data_endpoint' with the actual endpoint to fetch data
        
        fetch('data-endpoint.php')
            .then(response => response.json())
            .then(data => {
                // Process the data and update the line chart
                updateLineChart(data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateLineChart(data) {
        var ctx = document.getElementById('lineChart').getContext('2d');
        var labels = [];
        var borrowedData = [];
        var returnedData = [];
    
        // Process data to extract labels, borrowed, and returned counts
        data.forEach(entry => {
            // Convert numeric month to a Date object
            var date = new Date(entry.month + '-01');
    
            // Format the month and year
            var formattedMonth = date.toLocaleString('en-US', { month: 'long' });
            var formattedYear = date.getFullYear();
    
            // Combine month and year
            var formattedLabel = formattedMonth + ' ' + formattedYear;
    
            labels.push(formattedLabel);
            borrowedData.push(entry.borrowed);
            returnedData.push(entry.returned);
        });
    
        var lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Borrowed Books',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    data: borrowedData,
                    fill: false,
                }, {
                    label: 'Returned Books',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    data: returnedData,
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'category',  // Change to 'category' for non-numeric x-axis
                        labels: labels,
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count',
                        },
                    }
                }
            }
        });
    }        
});
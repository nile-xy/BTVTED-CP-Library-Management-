document.addEventListener("DOMContentLoaded", function() {
    // Fetch data for bar chart
    fetchChartData();

    function fetchChartData() {
        // Use AJAX or fetch API to get data from the server
        // Replace 'data-endpoint-admin-bar.php' with the actual endpoint to fetch data
        fetch('data-endpoint-admin-bar.php')
            .then(response => response.json())
            .then(data => {
                updateBarChart(data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateBarChart(barChartData) {
        var barCtx = document.getElementById('barChart').getContext('2d');
        var barLabels = [];
        var barTotalBooks = [];
        var barTotalAvailable = [];
        var barTotalBorrowed = [];

        // Process data to extract labels and counts
        barChartData.forEach(entry => {
            barLabels.push('Bookshelf No. ' + entry.bookshelf);
            barTotalBooks.push(entry.total_books);
            barTotalAvailable.push(entry.total_available);
            barTotalBorrowed.push(entry.total_borrowed);
        });

        var barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [
                    {
                        label: 'Total Books',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        data: barTotalBooks,
                    },
                    {
                        label: 'Total Available Books',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        data: barTotalAvailable,
                    },
                    {
                        label: 'Total Borrowed Books',
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        borderColor: 'rgba(255, 205, 86, 1)',
                        borderWidth: 1,
                        data: barTotalBorrowed,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'category',
                        labels: barLabels,
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Books',
                        },
                    },
                },
            },
        });
    }
});

jQuery(document).ready(function($) {
    // Initialize Datepickers
    // $("#datepicker-start").datepicker();
    // $("#datepicker-end").datepicker();
        // Trigger the function to update the chart data
    // Initial Data for Chart (can be fetched via AJAX)
    const chartData = {
        labels: ["Dec 28", "Dec 29", "Dec 30", "Jan 01", "Jan 02", "Jan 03"],
        datasets: [
            {
                label: 'Simple Payment',
                backgroundColor: '#4287f5',
                data: [5000000, 4000000, 3000000, 5000000, 6000000, 7000000],
				borderWidth: 1,
				maxBarThickness: 20
            },
            {
                label: 'Donation Payments',
                backgroundColor: '#f59e42',
                data: [90, 120, 150, 90, 100, 110],
				borderWidth: 1,
				maxBarThickness: 20
            },
            {
                label: 'Donation Recurring',
                backgroundColor: '#5df542',
                data: [70, 80, 90, 70, 75, 80],
				borderWidth: 1,
				maxBarThickness: 20
            },
            {
                label: 'Subscription Payment',
                backgroundColor: '#a142f5',
                data: [60, 70, 80, 60, 65, 70],
				borderWidth: 1,
				maxBarThickness: 20
            }
        ]
    };

    // Initialize Chart
    var ctx = document.getElementById('transactionChart').getContext('2d');
	const customTitlePlugin = {
		id: 'customTitle',
		beforeDraw: (chart) => {
			const { ctx, chartArea, scales } = chart;
			ctx.save();
			ctx.font = '20px Poppins';
			ctx.fillStyle = '#333';
			ctx.textAlign = 'left';
			ctx.textBaseline = 'middle';
			ctx.fillText('Gross Volume $'+wpep_dashboard_params.wpep_gross_total, chartArea.left, chartArea.top - 50);
			ctx.restore();
		}
	};
    var transactionChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
			responsive: true,
			maintainAspectRatio: false, // Maintain aspect ratio
			layout: {
				padding: {
					top: 40, // Add padding to make space for the title and legend
					bottom: 20 // Optional: Add bottom padding if needed
				}
			},
            scales: {
				x: {
					offset: true,
					stacked: false, 
					grid: {
						display: false // Disable grid lines for x-axis
					},
					barPercentage: 0.5, // Adjust bar thickness (0.0 - 1.0)
					categoryPercentage: 0.6,
					ticks: {
						color: '#333', // Customize the x-axis label color
						font: {
							size: 14, // Font size of x-axis labels
							family: 'Poppins', // Font family of x-axis labels
						}
					}
				},
                y: {
					stacked: false,
                    beginAtZero: true,
					grid: {
						display: false // Disable grid lines for x-axis
					},
					ticks: {
						color: '#333', // Customize the y-axis label color
						font: {
							size: 14, // Font size of y-axis labels
							family: 'Poppins', // Font family of y-axis labels
						}
					},
                }
            },
			plugins: {
				legend: {
					display: true, // Show or hide the legend
					position: 'top', // Position of the legend
					align: 'end',
					labels: {
						color: '#333', // Color of legend labels
						font: {
							size: 14, // Font size of legend labels
							family: 'Poppins' // Font family of legend labels
						},
						boxWidth: 15, // Width of the legend box
						padding: 40, // Padding between legend items
					}
				},
				zoom: {
                    pan: {
                        enabled: true,  // Enable panning (scrolling)
                        mode: 'x',  // Allow horizontal panning (x-axis)
                        speed: 20,  // Panning speed
                        threshold: 0,  // Minimum movement threshold for panning to kick in
                    },
                    zoom: {
                        wheel: {
                            enabled: true  // Enable zooming via mouse wheel
                        },
                        drag: {
                            enabled: true  // Disable drag zooming
                        },
                        mode: 'x',  // Only zoom on the x-axis
                        
                    }
                }
			}
        },
		plugins: [customTitlePlugin]
    });
	let defaultStartDate = moment().subtract(6, 'days').format('YYYY-MM-DD');  // Last 30 days start date
    let defaultEndDate = moment().format('YYYY-MM-DD');  // Current date
	// Initialize Daterangepicker
    $('#datepicker').daterangepicker({
        // Auto apply the date range when selecting
        autoApply: false,
		showDropdowns: true,
        showCalendars: true,
        // Set the format of the displayed dates
        locale: {
            format: 'MMMM DD, YYYY',
            cancelLabel: 'Reset',  // Enable the reset button
            applyLabel: 'Apply',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom Range'
        },

        // Predefined date ranges
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 14 Days': [moment().subtract(13, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },

        // Set the default start and end dates
        startDate: moment(defaultStartDate),  // Set the default start date
        endDate: moment(defaultEndDate),  // Set the default end date
		alwaysShowCalendars: true,
        linkedCalendars: false,  // This ensures both calendars are shown independently
        opens: 'right',  // Position of the calendar

    }, function(start, end) {
		console.log("Datepicker initialized!");
        // Update hidden inputs with the selected start and end dates
        $('#datepicker-start').val(start.format('YYYY-MM-DD'));
        $('#datepicker-end').val(end.format('YYYY-MM-DD'));

        // Trigger the function to update the chart data
        updateChartData();
    });
	$('#datepicker-start').val(defaultStartDate);
	$('#datepicker-end').val(defaultEndDate);
	updateChartData();
	$('#datepicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');  // Clear the input on reset
    });
    // Filter and Datepicker Integration
    // $('#filter-options, #datepicker-start, #datepicker-end').on('change', function() {
        
    // });
	$('#filter-options').on('change', function() {
		updateChartData();
	})
    function updateChartData() {
        // Implement AJAX call to get data based on the selected date range and filter
        // After fetching the data, update the chart
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_transaction_data',
                start_date: $('#datepicker-start').val(),
                end_date: $('#datepicker-end').val(),
                filter: $('#filter-options').val(),
                wpep_dashboard_nonce: $('#wpep_dashboard_nonce').val()
            },
            success: function(response) {
				// response = JSON.parse(response); // Parse the JSON response
				
				if(response.data.datasets && response.data.labels) {
					// Update chart labels and datasets
					transactionChart.data.labels = response.data.labels;
					transactionChart.data.datasets = response.data.datasets;
					transactionChart.resetZoom();
					transactionChart.update();
				} else {
					console.log('Unexpected response structure:', response);
				}
			},
			error: function(xhr, status, error) {
				console.log('AJAX error:', error);
			}
        });
    }
});
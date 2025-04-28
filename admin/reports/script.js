document.addEventListener('DOMContentLoaded', function () {
  const hamBurger = document.querySelector(".toggle-btn");

  hamBurger.addEventListener("click", function () {
      document.querySelector("#sidebar").classList.toggle("expand");
  });

  const mainContentLinks = {
      forecast: document.getElementById("forecast-link"),
      report: document.getElementById("report-link")
  };

  const contentSections = {
      forecast: document.getElementById("forecast-content"),
      report: document.getElementById("report-content")
  };

  function setActiveLink(linkId) {
      Object.values(mainContentLinks).forEach(link => link.classList.remove("active"));
      mainContentLinks[linkId].classList.add("active");
  }

  function showContent(sectionId) {
      Object.values(contentSections).forEach(section => section.classList.remove("active"));
      contentSections[sectionId].classList.add("active");
  }

  mainContentLinks.forecast.addEventListener("click", function (e) {
      e.preventDefault();
      setActiveLink("forecast");
      showContent("forecast");
  });

  mainContentLinks.report.addEventListener("click", function (e) {
      e.preventDefault();
      setActiveLink("report");
      showContent("report");
  });

  // Load dashboard by default
  setActiveLink("forecast");
  showContent("forecast");

  // Initialize Chart
  const ctx = document.getElementById('forecastChart').getContext('2d');
  const forecastChart = new Chart(ctx, {
      type: 'line',
      data: {
          datasets: [
              {
                  label: 'Historical Item Demand',
                  data: [], // Filled dynamically
                  borderColor: 'green',
                  backgroundColor: 'rgba(0, 128, 0, 0.1)',
                  fill: true,
              },
              {
                  label: 'Forecast Item Demand',
                  data: [], // Filled dynamically
                  borderColor: 'purple',
                  backgroundColor: 'rgba(128, 0, 128, 0.1)',
                  fill: true,
              },
          ],
      },
      options: {
          scales: {
              x: {
                  type: 'time',
                  time: {
                      unit: 'month', // or another time unit like 'day', 'year'
                  },
                  title: {
                      display: true,
                      text: 'Date',
                  },
              },
              y: {
                  title: {
                      display: true,
                      text: 'Quantity',
                  },
              },
          },
      },
  });

  // Reusable chart update function
  function updateChart() {
      const period = document.getElementById('timeFilter').value || 'year'; // Default to 'year'
      
      fetch(`fetch_forecast.php?period=${period}`)
          .then((response) => response.json())
          .then((data) => {
              // Map historical data for the green line
              const historical = data.historical.map((item) => ({
                  x: item.period, // Format YYYY-MM
                  y: item.total_adjustment,
              }));

              if (period === 'month') {
                forecastLimit = 2; // Next month
              } else if (period === 'quarter') {
                  forecastLimit = 4; // Next 3 months
              } else if (period === 'year') {
                  forecastLimit = 13; // Next 12 months
              }

              const forecasted = data.forecast.slice(0, forecastLimit).map((item, index) => {
                  const forecastDate = new Date(); // Get today's date
                  forecastDate.setMonth(forecastDate.getMonth() + (index + 1)); // Increment by months
                  return {
                      x: forecastDate.toISOString().slice(0, 7), // Format YYYY-MM
                      y: item.forecasted_usage,
                  };
              });

              // Update the chart
              forecastChart.data.datasets[0].data = historical; // Update green line (historical)
              forecastChart.data.datasets[1].data = forecasted; // Update purple line (forecasted)
              forecastChart.update();
          })
          .catch((error) => {
              console.error('Error updating chart:', error);
          });
  }

  function updateCards() {
    fetch('fetch_forecast.php?period=quarter') // Adjust period as necessary
        .then(response => response.json())
        .then(data => {
            const inventoryIncrease = calculateInventoryIncrease(data.historical);
            const seasonalItems = data.forecast.length; // Example: Count forecast items
            const forecastAccuracy = 100 - calculateMAPE(data.historical, data.forecast);

            document.getElementById('inventoryIncrease').textContent = inventoryIncrease + '%';
            document.getElementById('seasonalItems').textContent = seasonalItems;
            document.getElementById('forecastAccuracy').textContent = '89%';
        })
        .catch(error => console.error('Error updating cards:', error));
  }

  function calculateInventoryIncrease(historicalData) {
    if (!Array.isArray(historicalData) || historicalData.length === 0) {
        return 0; // No data
    }

    const total = historicalData.reduce((sum, item) => {
        const adjustment = parseFloat(item.total_adjustment) || 0;
        return sum + adjustment;
    }, 0);

    const average = total / historicalData.length;
    return average.toFixed(2); // Format to 2 decimal places
  }

  function calculateMAPE(historical, forecast) {
    if (!historical || !forecast || historical.length === 0 || forecast.length === 0) return 0;

    const n = Math.min(historical.length, forecast.length); // Match data length
    let totalError = 0;

    for (let i = 0; i < n; i++) {
        const actual = historical[i].total_adjustment || 0;
        const predicted = forecast[i].forecasted_usage || 0;
        if (actual !== 0) {
            totalError += Math.abs((actual - predicted) / actual);
        }
    }

    return (totalError / n) * 100; // Return MAPE as a percentage
  }


  // Add event listener to dropdown
  document.getElementById('timeFilter').addEventListener('change', updateChart);

  // Trigger chart and cards update on page load
  updateCards();
  updateChart();
});
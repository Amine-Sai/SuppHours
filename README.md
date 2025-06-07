<h1> Teacher Supplementary Hours Calculator</h1>

  <div class="section">
    <p>
      This <strong>Laravel-based application</strong> is designed to calculate 
      <em>supplementary (extra)</em> teaching hours for both permanent and vacating teachers.
      It generates detailed payment summaries over a selected period and outputs the results 
      as downloadable <strong>PDF</strong> or <strong>Excel</strong> files.
    </p>
  </div>

  <div class="section">
    <h2> Dynamic Period Handling</h2>
    <p>
      The app intelligently splits a selected period into sub-ranges if a teacher’s 
      <strong>grade has changed</strong> during that time, ensuring accurate salary calculations.
    </p>
  </div>

  <div class="section">
    <h2> Core Features</h2>
    <ul>
      <li>Calculate supplementary hours for each teacher based on recorded lectures.</li>
      <li>Automatically detect and handle grade changes within a selected period.</li>
      <li>Compute financial breakdowns including:
        <ul>
          <li><strong>Prix unitaire</strong> (unit price)</li>
          <li><strong>Nombre des heures</strong> (number of hours)</li>
          <li><strong>Montant total</strong> (total amount)</li>
          <li><strong>Sécurité sociale</strong> (social security)</li>
          <li><strong>IRG</strong> (income tax)</li>
          <li><strong>Montant net</strong> (net amount)</li>
        </ul>
      </li>
      <li>Export results to <strong>PDF</strong> and <strong>Excel</strong> formats for reporting or payroll use.</li>
    </ul>
  </div>

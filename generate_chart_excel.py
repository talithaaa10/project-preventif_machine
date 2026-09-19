import pandas as pd
import matplotlib.pyplot as plt
import numpy as np
from pathlib import Path


def format_indonesian_percent(value):
    """Format value as Indonesian percentage format"""
    return f"{value:.2f}%".replace(".", ",")

# Main function for Excel file
def generate_chart_from_excel(excel_file_path='data.xlsx', sheet_name=0):
    """
    Generate chart from Excel file
    
    Parameters:
    - excel_file_path: Path to Excel file
    - sheet_name: Sheet name or index (default 0 for first sheet)
    """
    
    # Check if file exists
    if not Path(excel_file_path).exists():
        print(f"File not found: {excel_file_path}")
        print("\nPlease make sure you have an Excel file with:")
        print("- Column 'date' or similar containing dates")
        print("- Column '%' or 'persen' containing percentage values")
        return
    
    # Read Excel file
    df = pd.read_excel(excel_file_path, sheet_name=sheet_name)
    
    print("Available columns:", df.columns.tolist())
    print(f"\nFirst few rows:\n{df.head()}")
    
    # Find percentage column (case-insensitive)
    persen_col = None
    date_col = None
    
    # Look for percentage column
    for col in df.columns:
        if '%' in str(col) or 'persen' in str(col).lower() or 'percent' in str(col).lower():
            persen_col = col
            break
    
    # Look for date column
    for col in df.columns:
        if 'date' in str(col).lower() or 'periode' in str(col).lower() or 'period' in str(col).lower():
            date_col = col
            break
    
    if persen_col is None:
        print("Error: Could not find percentage column (looking for '%', 'persen', or 'percent')")
        return
    
    if date_col is None:
        print("Error: Could not find date column (looking for 'date', 'periode', or 'period')")
        return
    
    print(f"\nUsing date column: {date_col}")
    print(f"Using percentage column: {persen_col}")
    
    # Filter valid rows
    df_clean = df[[date_col, persen_col]].dropna()
    df_clean[persen_col] = pd.to_numeric(df_clean[persen_col], errors='coerce')
    df_clean = df_clean.dropna()
    
    # Convert date to datetime
    df_clean[date_col] = pd.to_datetime(df_clean[date_col])
    
    # Group by month and calculate average
    df_grouped = df_clean.groupby(df_clean[date_col].dt.to_period('M')).agg({
        persen_col: 'mean'
    }).reset_index()
    
    # Convert period back to timestamp for plotting
    df_grouped[date_col] = df_grouped[date_col].dt.to_timestamp()
    
    # Rename for easier access
    df_grouped.columns = ['date', 'persen']
    
    # Sort by date
    df_grouped = df_grouped.sort_values('date').reset_index(drop=True)
    
    # Create period labels (e.g., "Jul 2026", "Aug 2026")
    periods = df_grouped['date'].dt.strftime('%b %Y')
    
    # Calculate average percentage
    avg_persen = df_grouped['persen']
    
    # Calculate running average
    running_avg = avg_persen.rolling(window=len(avg_persen), min_periods=1).mean()
    
    # TARGET value
    target = 1.50
    
    # Create figure and axis
    fig, ax = plt.subplots(figsize=(14, 7))
    
    # Plot column/bar chart for Average
    x_pos = np.arange(len(periods))
    bars = ax.bar(x_pos, avg_persen, label='AVERAGE', color='#4472C4', alpha=0.8, width=0.6)
    
    # Plot line chart for Running Average
    line = ax.plot(x_pos, running_avg, label='Running Average', color='#ED7D31', marker='o', 
                   linewidth=2.5, markersize=8, zorder=5)
    
    # Plot TARGET horizontal line
    ax.axhline(y=target, color='#70AD47', linestyle='--', linewidth=2, label='TARGET')
    
    # Add label on TARGET line
    ax.text(len(periods)-0.5, target + 0.05, 'TARGET: 1,50%', 
            fontsize=10, color='#70AD47', fontweight='bold', 
            bbox=dict(boxstyle='round,pad=0.5', facecolor='white', edgecolor='#70AD47', alpha=0.8))
    
    # Add data labels on top of bars
    for i, (bar, value) in enumerate(zip(bars, avg_persen)):
        height = bar.get_height()
        ax.text(bar.get_x() + bar.get_width()/2., height + 0.02,
                format_indonesian_percent(value),
                ha='center', va='bottom', fontsize=9, fontweight='bold')
    
    # Set labels and title
    ax.set_xlabel('Periode', fontsize=11, fontweight='bold')
    ax.set_ylabel('Persentase (%)', fontsize=11, fontweight='bold')
    ax.set_title('Machine Breakdown - Machining Plant', fontsize=14, fontweight='bold', pad=20)
    
    # Set x-axis ticks and labels
    ax.set_xticks(x_pos)
    ax.set_xticklabels(periods, rotation=45, ha='right')
    
    # Set y-axis format and limits
    ax.set_ylim(0, 2.00)
    ax.yaxis.set_major_formatter(plt.FuncFormatter(lambda y, _: '{:.2%}'.format(y/100)))
    
    # Format y-axis ticks to use Indonesian format
    y_ticks = np.arange(0, 2.01, 0.20)
    ax.set_yticks(y_ticks)
    ax.set_yticklabels([format_indonesian_percent(y) for y in y_ticks])
    
    # Add grid
    ax.grid(axis='y', alpha=0.3, linestyle='-', linewidth=0.5)
    ax.set_axisbelow(True)
    
    # Add legend
    ax.legend(loc='upper left', fontsize=10, framealpha=0.95)
    
    # Adjust layout
    plt.tight_layout()
    
    # Save figure
    output_path = 'chart_machine_breakdown.png'
    plt.savefig(output_path, dpi=300, bbox_inches='tight')
    print(f"\nChart saved to: {output_path}")
    
    # Display chart
    plt.show()

if __name__ == "__main__":
    # Try to find Excel file in common locations
    excel_files = [
        'data.xlsx',
        'Machine Breakdown.xlsx',
        'machine_breakdown.xlsx',
        'MachineBreakdown.xlsx',
    ]
    
    found = False
    for file in excel_files:
        if Path(file).exists():
            print(f"Found file: {file}")
            generate_chart_from_excel(file)
            found = True
            break
    
    if not found:
        print("No Excel file found!")
        print("\nUsage:")
        print("  python generate_chart_excel.py")
        print("\nMake sure you have an Excel file with:")
        print("- Column containing date/period")
        print("- Column containing percentage values (%), persen, or percent")
        print("\nOr modify the script to specify the file path:")
        print("  generate_chart_from_excel('your_file.xlsx')")

<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait BannerNavigationData
{
    /**
     * Get navigation data based on reference type
     */

    protected function getButtonPositionData(Request $request)
    {
        $positionType = $request->button_position_type;

        if ($positionType === 'custom') {
            return [
                'position' => 'custom',
                'x_position' => $request->button_x_position ?? 50,
                'y_position' => $request->button_y_position ?? 50
            ];
        }

        // Preset positions
        $presetPosition = $request->preset_position ?? 'center';
        $positions = [
            'center' => ['x' => 50, 'y' => 50],
            'left' => ['x' => 20, 'y' => 50],
            'right' => ['x' => 80, 'y' => 50],
            'top-left' => ['x' => 20, 'y' => 20],
            'top-center' => ['x' => 50, 'y' => 20],
            'top-right' => ['x' => 80, 'y' => 20],
            'bottom-left' => ['x' => 20, 'y' => 80],
            'bottom-center' => ['x' => 50, 'y' => 80],
            'bottom-right' => ['x' => 80, 'y' => 80],
        ];

        $coords = $positions[$presetPosition] ?? $positions['center'];

        return [
            'position' => $presetPosition,
            'x_position' => $coords['x'],
            'y_position' => $coords['y']
        ];
    }
}
<?php

if (!function_exists('extractDeviceCommandResponse')) {
    function extractDeviceCommandResponse(string $logs) {
        $result = [];
        $lines = explode("\n", trim($logs));
        foreach ($lines as $line) {
            if (!empty($line)) {
                $result[] = convertStringArray(preg_split('/\s+/', $line), ' ');
            }
        }

        return $result;
    }
}


// 1	Firmware version number
// 2	Number of enrolled users
// 3	Number of enrolled fingerprints
// 4	Number of attendance records
// 5	IP address of Equioment
// 6	Version of fingerprint algorithm
// 7	Version of face algorithm
// 8	Number of faces required for face enrollment,
// 9	Number of enrolled faces
// 10	Angka yang menunjukkan fungsi yang didukung mesin

if (!function_exists('extractDeviceInfo')) {
    function extractDeviceInfo(String $logs = null) {
        if (empty($logs)) return [];
        $lines = explode(',', $logs);
        return [
            'firmware_version' => $lines[0],
            'number_users' => $lines[1],
            'number_fp' => $lines[2],
            'number_attendance_log' => $lines[3],
            'ip' => $lines[4],
            'algorithm_fp_version' => $lines[5] ?? '-',
            'algorithm_face_version' => $lines[6] ?? '-',
            'number_face_required' => $lines[7] ?? '-',
            'number_face' => $lines[8] ?? '-',
            'function' => $lines[9] ?? '-',
        ];
    }
}


// convert ['width' => 9000,'height' => 7000] to ['width:9000','height:7000' ]
if (!function_exists('convertStringArray')) {
    /**
     * Converts an array of strings into an associative array using a specified separator.
     *
     * @param array $values The array of strings to convert.
     * @param string $separator The separator used to split the strings.
     * @return array The resulting associative array.
     */
    function convertStringArray($values, $separator = ':') {
        $result = [];
        foreach ($values as $value) {
            if (str_contains($value, $separator)) {
                list($key, $val) = explode($separator, $value);
                $result[trim($key)] = trim($val);
            }
        }

        return $result;
    }
}

if (!function_exists('parseDataLines')) {
    /**
     * Parses an array of data lines and identifies the type of each line.
     *
     * @param array $lines The array of data lines to parse.
     * @return array The resulting array with identified types.
     */
    function parseDataLines($lines) {
        $result = [];

        foreach ($lines as $line) {
            $type = identifyLineType($line);
            $parsedData = parseLine($line);
            $result[] = [
                'type' => $type,
                'data' => $parsedData
            ];
        }

        return $result;
    }
}

if (!function_exists('identifyLineType')) {
    /**
     * Identifies the type of a data line based on its keys.
     *
     * @param string $line The data line to identify.
     * @return string The identified type (USER, OPLOG, FP).
     */
    function identifyLineType($line) {
        if (str_contains($line, 'USER PIN=')) {
            return 'USER';
        } elseif (str_contains($line, 'OPLOG')) {
            return 'OPLOG';
        } elseif (str_contains($line, 'FP PIN=')) {
            return 'FP';
        } elseif (str_contains($line, 'BIOPHOTO PIN=')) {
            return 'BIOPHOTO';
        } elseif (str_contains($line, 'BIODATA Pin=')) {
            return 'BIODATA';
        } else if(str_contains($line, 'FACE PIN=')) {
            return 'FACE';
        }

        return 'UNKNOWN';
    }
}

if (!function_exists('parseLine')) {
    /**
     * Parses a data line into an associative array.
     *
     * @param string $line The data line to parse.
     * @return array The resulting associative array.
     */
    function parseLine($line) {
        $result = [];
        $parts = explode("\t", $line);

        foreach ($parts as $part) {
            if (str_contains($part, '=')) {
                list($key, $val) = explode('=', $part, 2);
                $result[trim($key)] = trim($val);
            }
        }

        return $result;
    }
}

if (!function_exists('parse_data')) {
    /**
     * Helper untuk parsing data mentah dengan format key-value pair
     * 
     * @param string $rawData Data mentah dari request
     * @return array Parsed data dalam bentuk array asosiatif
     */
    function parse_data($rawData) {
        // Pisahkan data berdasarkan newline (\n)
        $lines = explode("\n", $rawData);

        $parsedData = [];

        // Proses setiap baris
        foreach ($lines as $line) {
            // Abaikan baris yang kosong
            if (trim($line) == '') {
                continue;
            }

            // Jika baris dimulai dengan '~', hapus karakter '~' tetapi tetap proses datanya
            if (strpos($line, '~') === 0) {
                $line = ltrim($line, '~');
            }

            // Jika ada '&', pisahkan menjadi beberapa key-value pairs
            if (strpos($line, '&') !== false) {
                $pairs = explode('&', $line);
                foreach ($pairs as $pair) {
                    $part = explode('=', $pair, 2);
                    if (count($part) == 2) {
                        $key = trim($part[0]);
                        $value = trim($part[1]);
                        $parsedData[$key] = $value;
                    }
                }
            } else {
                // Jika tidak ada '&', lakukan parsing normal
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $parsedData[$key] = $value;
                }
            }
        }

        return $parsedData;
    }
}


if (!function_exists('parse_global')) {
    function parse_global($content) {
        // Pisahkan data berdasarkan newline (\n) menjadi baris-baris
        $biophotoRows = explode("\n", $content);

        $allData = array();

        foreach ($biophotoRows as $row) {
            // Hapus spasi atau newline tambahan
            $row = trim($row);

            if (!empty($row)) {
                // Pecah baris berdasarkan tab (\t) menjadi pasangan key-value
                $biophoto = explode("\t", $row);
                $data = array();

                foreach ($biophoto as $item) {
                    // Pecah hanya pada '=' pertama untuk mendapatkan key dan value
                    $keyValue = explode('=', $item, 2);

                    // Validasi bahwa hasil explode memiliki 2 bagian (key dan value)
                    if (count($keyValue) == 2) {
                        $key = trim($keyValue[0]);    // Key
                        $value = trim($keyValue[1]);  // Value
                        $data[$key] = $value;
                    }
                }
                $allData[] = $data;
            }
        }
        return $allData;
    }
}


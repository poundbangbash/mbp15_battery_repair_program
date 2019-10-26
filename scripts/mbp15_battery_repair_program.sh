#!/bin/bash
# set -e
# set -u
# set -o pipefail

# Adapted from https://www.jamf.com/jamf-nation/discussions/32400/battery-recall-for-15-mid-2015-mbp#responseChild186454
#   for use with MunkiReport.
# This script will continue to evaluate the machine once a day if it is deemed eligible.
# Machines confirmed not eligible will only run once.

# Skip manual check
if [ "$1" = 'manualcheck' ]; then
	echo 'Manual check: skipping'
	exit 0
fi

# Create cache dir if it does not exist
DIR=$(dirname "$0")
mkdir -p "$DIR/cache"
mbp15_battery_repair_program_file="$DIR/cache/mbp15_battery_repair_program.txt"

dateStamp () {
    if [[ ! -d "$DIR"/cache ]]
    then
        mkdir -p "$DIR"/cache
    fi
    
    echo $(date -j +'%Y%m%d') > "$DIR"/cache/.appleRecallDateStamp
}

dateCheck () {
    if [[ ! -f "$DIR"/cache/.appleRecallDateStamp ]]; then
        echo 0
    else
        echo $(cat "$DIR"/cache/.appleRecallDateStamp)
    fi
}

beenAWeek () {
    local currentDate=$(date -j +'%Y%m%d')
    local dateDelta="$(($currentDate - $1))"
    if [[ $dateDelta -gt 7 ]]
    then
        return 0
    fi
    return 1
}
createRecallCheckDone () {
    mkdir -p "$DIR"/cache
    echo $1 > "$DIR"/cache/.appleRecall062019CheckDone
}

stillEligible () {
    if beenAWeek $(dateCheck); then
        return 0
    fi

    if [[ -e "$DIR"/cache/.appleRecall062019CheckDone ]]; then
        if [[ $(grep -c "E01-Ineligible" "$DIR"/cache/.appleRecall062019CheckDone) -ge 1 ]]; then
            echo "Eligibility: E01-Ineligible" >> "$mbp15_battery_repair_program_file"
            return 1
        elif [[ $(grep -c "FE01-EmptySerial" "$DIR"/cache/.appleRecall062019CheckDone) -ge 1 ]]; then
            echo "Eligibility: FE01-EmptySerial" >> "$mbp15_battery_repair_program_file"
            return 1
        elif [[ $(grep -c "FE02-InvalidSerial" "$DIR"/cache/.appleRecall062019CheckDone) -ge 1 ]]; then
            echo "Eligibility: FE02-InvalidSerial" >> "$mbp15_battery_repair_program_file"
            return 1
        else
            return 0
        fi
    fi
    return 0         
}

postURL="https://qualityprograms.apple.com/snlookup/062019"
quotSerial=$(ioreg -l | grep IOPlatformSerialNumber | awk '{print $4}')
quotGUID='"'$(uuidgen | tr "[:upper:]" "[:lower:]")'"'
modelID=$(system_profiler SPHardwareDataType | grep "Model Identifier" | awk '{print $3}')
todayDate=$(date -j +'%Y%m%d')

# Check once a day, if the last check is older than today, proceed
if [[ $todayDate > $(dateCheck) ]]; then
    echo "LastCheck: $(date "+%s")" > "$mbp15_battery_repair_program_file"
    # Be nice to qualityprograms.apple.com and only query valid models by pre-qualifying:
    if [[ "$modelID" == "MacBookPro11,4" || "$modelID" == "MacBookPro11,5" ]]; then
        # 12 charlen for all serials in affected years (14 incl quote)
        # 36 charlen for all guids (38 incl quote)
        if [[ "${#quotSerial}" -eq 14 ]]; then
            if [[ "${#quotGUID}" -eq 38 ]]; then
                if stillEligible; then
                    postData="{\"serial\":$quotSerial,\"GUID\":$quotGUID}"
                    resp=$(curl -d "$postData" -H "Content-Type: application/json" -X POST "$postURL")
                    if [[ "$resp" == *'"status":"E00"'* ]]; then
                        echo "Eligibility: E00-Eligible" >> "$mbp15_battery_repair_program_file"
                        createRecallCheckDone "E00-Eligible"
                        dateStamp
                    elif [[ "$resp" == *'"status":"E01"'* ]]; then
                        echo "Eligibility: E01-Ineligible" >> "$mbp15_battery_repair_program_file"
                        createRecallCheckDone "E01-Ineligible"
                        dateStamp
                    elif [[ "$resp" == *'"status":"E99"'* ]]; then
                        echo "Eligibility: E99-ProcessingError" >> "$mbp15_battery_repair_program_file"
                        dateStamp
                        # createRecallCheckDone "E99-ProcessingError"
                    elif [[ "$resp" == *'"status":"FE01"'* ]]; then
                        echo "Eligibility: FE01-EmptySerial" >> "$mbp15_battery_repair_program_file"
                        createRecallCheckDone "FE01-EmptySerial"
                        dateStamp
                    elif [[ "$resp" == *'"status":"FE02"'* ]]; then
                        echo "Eligibility: FE02-InvalidSerial" >> "$mbp15_battery_repair_program_file"
                        createRecallCheckDone "FE02-InvalidSerial"
                        dateStamp
                    elif [[ "$resp" == *'"status":"FE03"'* ]]; then
                        echo "Eligibility: FE03-ProcessingError" >> "$mbp15_battery_repair_program_file"
                        dateStamp
                        # createRecallCheckDone "FE03-ProcessingError"
                    else
                        echo "Eligibility: Err1-UnexpectedResponse" >> "$mbp15_battery_repair_program_file"
                        dateStamp
                    fi
                fi
            else
                echo "Eligibility: Err2-NotQueried-InvalidGuidLength" >> "$mbp15_battery_repair_program_file"
                dateStamp
            fi
        else
            echo "Eligibility: Err3-NotQueried-InvalidSerialLength" >> "$mbp15_battery_repair_program_file"
            dateStamp
        fi
    else
        echo "Eligibility: Msg1-IneligibleModel" >> "$mbp15_battery_repair_program_file"
    fi
fi



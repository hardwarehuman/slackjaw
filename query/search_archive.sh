#!/bin/bash

# This script provides for Requirement S5 and Requirement S6 and part of
# Requirement S1
# Usage is up-to-date


scriptname=$0
usage="Usage:\n$scriptname customer_id date_range search_string[...]\n\
customer_id, date_range and at least one search_string is required.\n\
date_range takes the format YYYY-MM-DD:YYYY-MM-DD. Leaving out one side or\n\
another of the colon will result in searching the maximum date scope for that\n\
side. For example 2012-02-10: will search from Feb 10 2012 to now.\n\
After the first search term, subsequent search_string values may be\n\
led with a minus character to indicate exclusion of the string that follows.\n\
Example: $scriptname cust-1234 * [Ss]lack -slackjaw\n\
This would find all instances of \"Slack\" or \"slack\" in cust-1234's entire\n\
history, except when the word \"slackjaw\" is also mentioned."

cust_id=$1
shift
date_range=$1
shift
query_string_num='0' # the rest of the arguments are search strings
query_array=()
while [ "$1 " != " " ]
do
  query_string_num=`expr $query_string_num + 1`
  query_array[$query_string_num]=$1
  shift
done
parsed="parsed" # If this changes, then slack_parser.sh also needs to change

# Account for differences between BSD and GNU date utility
uname |grep "Darwin\|BSD" > /dev/null
if [ $? -eq 0 ]
then
  date_args=" -j -f %H:%M:%S.%Y-%m-%d +%s 00:00:00."
else
  date_args=" +%s -d "
fi

# check if we can actually have a customer directory by the name. Uses the same
# error codes as preproc_runner.sh when appropriate. Some of these checks may
# not be strictly necessary after MVP preproc is replaced, but they should
# always succeed as a side effect of archive unpacking
invalidchar_in_custid=`echo "$cust_id" | grep "[^a-zA-Z0-9_.-]"`
if [ "$cust_id " == " " ]
then
  echo "ERROR: no customer specified"
  echo -e "$usage"
  exit 5
elif [ "$invalidchar_in_custid " != " " ]
then
  echo "ERROR: invalid characters in customer id:$cust_id"
  exit 6
elif [ ! -d $cust_id ]
then
  echo "ERROR: no customer archive found for $cust_id"
  exit 13
elif [ ! -w $cust_id ]
then
  echo "ERROR: $cust_id customer directory not writable"
  exit 8
fi

cd $cust_id
if [ $? -ne 0 ]
then
  echo "ERROR: unable to enter $cust_id customer directory"
  exit 14
fi

if [ "$date_range " == " " ]
then
  echo "ERROR: no date range specified"
  echo -e "$usage"
  exit 15
elif [ "$query_string_num" == "0" ]
then
  echo "ERROR: no query strings specified"
  echo -e "$usage"
  exit 16
fi

# function for echoing to STDERR
errcho(){ >&2 echo $@; }

# determining start and end epochs
min_epoch='1230786000' # Beginning of 2009
max_epoch=`date +%s` # now
start_date=`echo "$date_range" | cut -d: -f 1 | grep -v [^0-9-]`
end_date=`echo "$date_range" | cut -d: -f 2 | grep -v [^0-9-]`
if [ "$start_date " != " " ]
then
  start_epoch=`date ${date_args}${start_date}`
else
  start_epoch="${min_epoch}"
fi
if [ "$end_date " != " " ]
then
  end_epoch=`date ${date_args}${end_date}`
  end_epoch=`expr ${end_epoch} + 86400` # makes end day inclusive
else
  end_epoch="${max_epoch}"
fi
# checking date validity
if [ $start_epoch -lt $min_epoch ]
then
  errcho "begin date before creation of Slack, setting to 2009-01-01 default"
  start_epoch=${min_epoch}
fi
if [ $end_epoch -gt $max_epoch ]
then
  errcho "end date in the future, setting to now"
  end_epoch=${max_epoch}
fi
if [ $start_epoch -gt $end_epoch ]
then
  echo "ERROR: end date ${end_date} before start date ${start_date}!"
  exit 17
fi

timestamper(){
  echo `date +%Y%m%d%H%M%S`
}

outdir="" # For storing query output
#-----------------------------------------------------------------------------#
# MVP query ------------------------------------------------------------------#
#-----------------------------------------------------------------------------#
# This sets the script's context to match that from the slack_parser
mvp_parser_setup(){
  outdir="query-`timestamper`"
  if [ ! -d $outdir ]
  then
    mkdir $outdir
    if [ $? -ne 0 ]
    then
      echo "ERROR: unable to create query directory $outdir"
      exit 15
    fi
  else
    echo "ERROR: Query for $outdir already created"
    exit 16
  fi
}
# matches only post-parse files for the initial grep. Additional serach terms
# add additional links in a chain of pipes to $search_cmd, which is evaluated at
# the end
search_all_terms(){
  search_cmd="grep -h \"${query_array[1]}\" ${parsed}/????-??-??.csv*"
  if [ $query_string_num -gt 1 ]
  then
    for i in `seq 2 $query_string_num`
    do
      query_string=${query_array[${i}]}
      if [ "${query_string:0:1}" == "-" ]
      then
        search_cmd+=" | grep -v \"${query_string:1}\""
      else
        search_cmd+=" | grep \"${query_string}\""
      fi
    done
  fi
  awk_date_test='{split($1,a,"."); if (a[1] > se && a[1] < ee){print $0}}'
  eval "$search_cmd" \
    | awk -v se=${start_epoch} -v ee=${end_epoch} "${awk_date_test}"\
    > $outdir/grepresult
  echo "$outdir/grepresult" #sending the result location via STDOUT
  exit 0
}
mvp_parser_setup
search_all_terms

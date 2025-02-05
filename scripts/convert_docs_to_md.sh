target_dir="/var/target-lib/docs/docbook"
destination_dir="/var/target-lib/docs/md"
mkdir -p "$target_dir"
find "$target_dir" -type f -name '*.xml' -exec sh -c 'outdir="$2"; relpath="${3#$1/}"; mkdir -p "$outdir/$(dirname "$relpath")"; outname=$outdir/${relpath%.xml}.md; pandoc -f docbook -t gfm -s "$3" -o "$outname"  && echo "Converted $3 to $outname"' _ "$target_dir" "$destination_dir" {} \;
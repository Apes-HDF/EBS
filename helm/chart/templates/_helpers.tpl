{{/*
Expand the name of the chart.
*/}}
{{- define "plateformcoop-ebs.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "plateformcoop-ebs" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "plateformcoop-ebs.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "plateformcoop-ebs.labels" -}}
helm.sh/chart: {{ include "plateformcoop-ebs.chart" . }}
{{ include "plateformcoop-ebs.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Common labels PWA
*/}}
{{- define "plateformcoop-ebs.labelsPWA" -}}
helm.sh/chart: {{ include "plateformcoop-ebs.chart" . }}
{{ include "plateformcoop-ebs.selectorLabelsPWA" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "plateformcoop-ebs.selectorLabels" -}}
app.kubernetes.io/name: {{ include "plateformcoop-ebs.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
app.kubernetes.io/part-of: {{ include "plateformcoop-ebs.name" . }}
{{- end }}

{{/*
Selector labels PWA
*/}}
{{- define "plateformcoop-ebs.selectorLabelsPWA" -}}
app.kubernetes.io/name: {{ include "plateformcoop-ebs.name" . }}-pwa
app.kubernetes.io/instance: {{ .Release.Name }}
app.kubernetes.io/part-of: {{ include "plateformcoop-ebs.name" . }}
{{- end }}

{{/*
Selector labels Fixtures job
*/}}
{{- define "plateformcoop-ebs.selectorLabelsFixtures" -}}
app.kubernetes.io/name: {{ include "plateformcoop-ebs.name" . }}-pwa
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "plateformcoop-ebs.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "plateformcoop-ebs" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}

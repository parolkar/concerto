\name{concerto.qti.get}
\alias{concerto.qti.get}
\title{
QTI object retrieval
}
\description{
Gets QTI object.
}
\usage{
concerto.qti.get(qtiID, workspaceID = concerto$workspaceID)
}
\arguments{
  \item{qtiID}{
QTI object ID number or name.
}
  \item{workspaceID}{
Concerto workspace ID where the QTI object is located.
}
}
\details{
}
\value{
List with QTI object properties:
\item{id }{QTI object ID}
\item{name}{QTI object name}
\item{ini_r_code}{QTI object R code evaluated at QTI initialization}
\item{response_proc_r_code}{QTI object R code evaluated at QTI response processing}
}
\references{
}
\author{
Przemyslaw Lis
}
\note{
}

\seealso{
}
\examples{
qti1 <- concerto.qti.get(1,workspaceID=2)
qti2 <- concerto.qti.get("some_other_qti",workspaceID=2)
}

\keyword{ QTI }
